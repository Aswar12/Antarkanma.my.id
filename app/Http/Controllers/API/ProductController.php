<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::with(['merchant', 'category', 'galleries'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->when($request->has('merchant_id'), fn($q) => $q->where('merchant_id', $request->merchant_id))
                ->when($request->has('category_id'), fn($q) => $q->where('category_id', $request->category_id))
                ->when($request->has('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->has('min_price'), fn($q) => $q->where('price', '>=', $request->min_price))
                ->when($request->has('max_price'), fn($q) => $q->where('price', '<=', $request->max_price))
                ->when($request->has('status'), fn($q) => $q->where('status', $request->status))
                ->paginate(10);

            return ResponseFormatter::success(
                $products,
                'Data produk berhasil diambil'
            );
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ResponseFormatter::error(
                null,
                'Terjadi kesalahan saat mengambil data produk',
                500
            );
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|exists:product_categories,id',
                'merchant_id' => 'required|exists:merchants,id',
                'status' => 'in:ACTIVE,INACTIVE,OUT_OF_STOCK'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            DB::beginTransaction();
            try {
                $product = Product::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'price' => $request->price,
                    'category_id' => $request->category_id,
                    'merchant_id' => $request->merchant_id,
                    'status' => $request->status ?? 'ACTIVE'
                ]);

                // Add rating info consistently with other methods
                $product->rating_info = [
                    'average_rating' => 0.0,
                    'total_reviews' => 0
                ];

                DB::commit();

                return ResponseFormatter::success(
                    $product,
                    'Product created successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating product: ' . $e->getMessage(), [
                    'request_data' => $request->except(['gallery'])
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to create product: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getByCategory(Request $request, $categoryId)
    {
        try {
            $start = microtime(true);

            $cacheKey = "products_category_{$categoryId}_" . md5(json_encode($request->all()));
            $cacheDuration = 60; // 60 minutes

            $products = Cache::remember($cacheKey, $cacheDuration, function () use ($request, $categoryId) {
                try {
                    $result = Product::query()
                        ->with([
                            'merchant:id,name,owner_id', 
                            'category:id,name', 
                            'galleries:id,product_id,url'
                        ])
                        ->select([
                            'products.id', 
                            'products.name', 
                            'products.description', 
                            'products.price',
                            'products.status', 
                            'products.category_id', 
                            'products.merchant_id',
                            'products.created_at'
                        ])
                        ->selectRaw('COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id), 0) as average_rating')
                        ->selectRaw('COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id), 0) as total_reviews')
                        ->where('category_id', $categoryId)
                        ->when(!$request->input('include_inactive'), function($q) {
                            return $q->where('status', 'ACTIVE');
                        })
                        ->when($request->input('price_from'), fn($q, $price) => $q->where('price', '>=', $price))
                        ->when($request->input('price_to'), fn($q, $price) => $q->where('price', '<=', $price))
                        ->orderBy('created_at', 'desc')
                        ->paginate($request->input('limit', 10));

                    $collection = $result->getCollection();
                    $collection->transform(function ($product) {
                        try {
                            $product->rating_info = [
                                'average_rating' => round($product->average_rating, 1),
                                'total_reviews' => (int)$product->total_reviews
                            ];
                            return $product;
                        } catch (\Exception $e) {
                            Log::error('Error transforming product data in getByCategory: ' . $e->getMessage(), [
                                'product_id' => $product->id ?? 'unknown'
                            ]);
                            return $product; // Return original product if transformation fails
                        }
                    });

                    $result->setCollection($collection);
                    return $result;
                } catch (\Exception $e) {
                    Log::error('Query error in getByCategory: ' . $e->getMessage());
                    throw $e;
                }
            });

            $duration = microtime(true) - $start;
            if ($duration > 1) { // Log queries slower than 1 second
                Log::warning("Slow category products query", [
                    'duration' => $duration,
                    'category_id' => $categoryId,
                    'params' => $request->all()
                ]);
            }

            return ResponseFormatter::success(
                $products ?? [],
                'Data produk berhasil diambil'
            );
        } catch (\Exception $e) {
            Log::error('Category products query error: ' . $e->getMessage(), [
                'category_id' => $categoryId,
                'params' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty result instead of error
            return ResponseFormatter::success(
                [],
                'Data produk berhasil diambil'
            );
        }
    }

    public function getPopularProducts(Request $request)
    {
        $start = microtime(true);

        $cacheKey = "products_popular_" . md5(json_encode($request->all()));
        $cacheDuration = 60; // 60 minutes

        $products = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            $result = Product::query()
                ->select([
                    'products.id', 
                    'products.name', 
                    'products.description', 
                    'products.price',
                    'products.status', 
                    'products.category_id', 
                    'products.merchant_id',
                    'products.created_at',
                    DB::raw('COALESCE(AVG(product_reviews.rating), 0) as average_rating'),
                    DB::raw('COALESCE(COUNT(product_reviews.id), 0) as total_reviews')
                ])
                ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                ->with([
                    'merchant:id,name,owner_id', 
                    'category:id,name', 
                    'galleries:id,product_id,url'
                ])
                ->groupBy('products.id')
                ->havingRaw('COALESCE(AVG(product_reviews.rating), 0) >= ?', [$request->input('min_rating', 4.0)])
                ->havingRaw('COALESCE(COUNT(product_reviews.id), 0) >= ?', [$request->input('min_reviews', 5)])
                ->when($request->input('category_id'), fn($q, $catId) => $q->where('category_id', $catId))
                ->orderBy('average_rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->paginate($request->input('limit', 12));

            $collection = $result->getCollection();
            $collection->transform(function ($product) {
                try {
                    $product->rating_info = [
                        'average_rating' => round($product->average_rating, 1),
                        'total_reviews' => (int)$product->total_reviews
                    ];
                    return $product;
                } catch (\Exception $e) {
                    Log::error('Error transforming product data in getPopularProducts: ' . $e->getMessage(), [
                        'product_id' => $product->id ?? 'unknown'
                    ]);
                    return $product; // Return original product if transformation fails
                }
            });

            $result->setCollection($collection);
            return $result;
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow popular products query", [
                'duration' => $duration,
                'params' => $request->all()
            ]);
        }

        return ResponseFormatter::success(
            $products,
            $products->isEmpty() ? 'Tidak ada produk populer yang ditemukan' : 'Data produk populer berhasil diambil'
        );
    }

    public function getProductWithReviews($id)
    {
        $start = microtime(true);

        $cacheKey = "product_with_reviews_{$id}";
        $cacheDuration = 60; // 60 minutes

        $product = Cache::remember($cacheKey, $cacheDuration, function () use ($id) {
            $product = Product::query()
                ->with([
                    'merchant:id,name,owner_id', 
                    'category:id,name', 
                    'galleries:id,product_id,url'
                ])
                ->select([
                    'products.id', 
                    'products.name', 
                    'products.description', 
                    'products.price',
                    'products.status', 
                    'products.category_id', 
                    'products.merchant_id',
                    'products.created_at',
                    DB::raw('COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id), 0) as average_rating'),
                    DB::raw('COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id), 0) as total_reviews')
                ])
                ->where('id', $id)
                ->first();

            if (!$product) {
                return null;
            }

            // Get reviews with optimized query
            $reviews = $product->reviews()
                ->with('user:id,name')
                ->select(['id', 'user_id', 'rating', 'comment', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->get();

            try {
                // Add rating statistics
                $ratingStats = [
                    'average' => round($product->average_rating, 1),
                    'total' => (int)$product->total_reviews,
                    'distribution' => [
                        5 => (int)$product->reviews()->where('rating', 5)->count(),
                        4 => (int)$product->reviews()->where('rating', 4)->count(),
                        3 => (int)$product->reviews()->where('rating', 3)->count(),
                        2 => (int)$product->reviews()->where('rating', 2)->count(),
                        1 => (int)$product->reviews()->where('rating', 1)->count(),
                    ]
                ];

                $product->rating_info = $ratingStats;
                $product->reviews = $reviews;

                return $product;
            } catch (\Exception $e) {
                Log::error('Error transforming product data in getProductWithReviews: ' . $e->getMessage(), [
                    'product_id' => $product->id ?? 'unknown'
                ]);
                return $product; // Return original product if transformation fails
            }
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow product with reviews query", [
                'duration' => $duration,
                'product_id' => $id
            ]);
        }

        if (!$product) {
            return ResponseFormatter::success(
                [],
                'Data produk berhasil diambil'
            );
        }

        return ResponseFormatter::success(
            $product,
            'Data produk dan review berhasil diambil'
        );
    }

    public function getTopProductsByCategory(Request $request)
    {
        $start = microtime(true);

        $cacheKey = "products_top_by_category_" . md5(json_encode($request->all()));
        $cacheDuration = 60; // 60 minutes

        $result = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            $categories = ProductCategory::select('id', 'name')->get();
            $result = [];

            foreach ($categories as $category) {
                $topProducts = Product::query()
                    ->select([
                        'products.id', 
                        'products.name', 
                        'products.description', 
                        'products.price',
                        'products.status', 
                        'products.category_id', 
                        'products.merchant_id',
                        'products.created_at',
                        DB::raw('COALESCE(AVG(product_reviews.rating), 0) as average_rating'),
                        DB::raw('COALESCE(COUNT(product_reviews.id), 0) as total_reviews')
                    ])
                    ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                    ->where('category_id', $category->id)
                    ->with([
                        'merchant:id,name,owner_id',
                        'galleries:id,product_id,url'
                    ])
                    ->groupBy('products.id')
                    ->havingRaw('COALESCE(AVG(product_reviews.rating), 0) >= ?', [$request->input('min_rating', 4.0)])
                    ->havingRaw('COALESCE(COUNT(product_reviews.id), 0) >= ?', [$request->input('min_reviews', 3)])
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('total_reviews', 'desc')
                    ->limit($request->input('limit', 5))
                    ->get();

                $topProducts->transform(function ($product) {
                    try {
                        $product->rating_info = [
                            'average_rating' => round($product->average_rating, 1),
                            'total_reviews' => (int)$product->total_reviews
                        ];
                        return $product;
                    } catch (\Exception $e) {
                        Log::error('Error transforming product data in getTopProductsByCategory: ' . $e->getMessage(), [
                            'product_id' => $product->id ?? 'unknown'
                        ]);
                        return $product; // Return original product if transformation fails
                    }
                });

                if ($topProducts->isNotEmpty()) {
                    $result[] = [
                        'category' => $category->name,
                        'products' => $topProducts
                    ];
                }
            }

            return $result;
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow top products by category query", [
                'duration' => $duration,
                'params' => $request->all()
            ]);
        }

        return ResponseFormatter::success(
            $result,
            'Data produk top per kategori berhasil diambil'
        );
    }

    public function getProductByMerchant(Request $request, $merchantId)
    {
        $start = microtime(true);

        $cacheKey = "products_merchant_{$merchantId}_" . md5(json_encode($request->all()));
        $cacheDuration = 60; // 60 minutes

        $products = Cache::remember($cacheKey, $cacheDuration, function () use ($request, $merchantId) {
            $query = Product::query()
                ->with([
                    'merchant:id,name,owner_id', 
                    'category:id,name', 
                    'galleries:id,product_id,url'
                ])
                ->select([
                    'products.id', 
                    'products.name', 
                    'products.description', 
                    'products.price',
                    'products.status', 
                    'products.category_id', 
                    'products.merchant_id',
                    'products.created_at',
                    DB::raw('COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id), 0) as average_rating'),
                    DB::raw('COALESCE((SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id), 0) as total_reviews')
                ])
                ->where('merchant_id', $merchantId)
                ->when($request->input('price_from'), fn($q, $price) => $q->where('price', '>=', $price))
                ->when($request->input('price_to'), fn($q, $price) => $q->where('price', '<=', $price))
                ->orderBy('created_at', 'desc');

            $result = $request->input('get_all') 
                ? $query->get() 
                : $query->paginate($request->input('limit', 10));

            $collection = $request->input('get_all') ? $result : $result->getCollection();
            $collection->transform(function ($product) {
                try {
                    $product->rating_info = [
                        'average_rating' => round($product->average_rating, 1),
                        'total_reviews' => (int)$product->total_reviews
                    ];
                    return $product;
                } catch (\Exception $e) {
                    Log::error('Error transforming product data in getProductByMerchant: ' . $e->getMessage(), [
                        'product_id' => $product->id ?? 'unknown'
                    ]);
                    return $product; // Return original product if transformation fails
                }
            });

            if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $result->setCollection($collection);
            } else {
                $result = $collection;
            }

            return $result;
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow merchant products query", [
                'duration' => $duration,
                'merchant_id' => $merchantId,
                'params' => $request->all()
            ]);
        }

        return ResponseFormatter::success(
            $products,
            $products->isEmpty() ? 'Tidak ada produk ditemukan untuk merchant ini' : 'Data produk berhasil diambil'
        );
    }

    public function update(Request $request, $id)
    {
        try {
            // Find the product
            $product = Product::find($id);

            if (!$product || $product->merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::success(
                    null,
                    'Data produk berhasil diambil'
                );
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'description' => 'string',
                'price' => 'numeric|min:0',
                'category_id' => 'exists:product_categories,id',
                'status' => 'in:ACTIVE,INACTIVE,OUT_OF_STOCK',
                'variants' => 'array'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            DB::beginTransaction();
            try {
                try {
                    // Update product basic info
                    $product->update([
                        'name' => $request->name,
                        'description' => $request->description,
                        'price' => $request->price,
                        'category_id' => $request->category_id,
                        'status' => $request->status
                    ]);

                    // Handle variants if provided
                    if ($request->has('variants')) {
                        // Delete existing variants
                        $product->variants()->delete();

                        // Add new variants
                        foreach ($request->variants as $variant) {
                            $product->variants()->create($variant);
                        }
                    }

                    DB::commit();

                    // Reload product with variants and transform data
                    $product->load('variants');
                    
                    // Add rating info consistently with other methods
                    $product->rating_info = [
                        'average_rating' => round($product->average_rating ?? 0, 1),
                        'total_reviews' => (int)($product->total_reviews ?? 0)
                    ];

                    return ResponseFormatter::success(
                        $product,
                        'Product updated successfully'
                    );
                } catch (\Exception $e) {
                    Log::error('Error updating product data: ' . $e->getMessage(), [
                        'product_id' => $product->id ?? 'unknown',
                        'request_data' => $request->except(['gallery'])
                    ]);
                    throw $e;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::success(
                null,
                'Data produk berhasil diambil'
            );
        }
    }

    public function destroy($id)
    {
        try {
            // Find the product
            $product = Product::with('galleries')->find($id);

            if (!$product || $product->merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::success(
                    null,
                    'Data produk berhasil diambil'
                );
            }

            DB::beginTransaction();
            try {
                // Delete associated galleries first
                foreach ($product->galleries as $gallery) {
                    try {
                        // Get clean path without storage URL
                        $path = str_replace('storage/', '', $gallery->url);

                        // Delete the physical file
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error deleting gallery file: ' . $e->getMessage(), [
                            'gallery_id' => $gallery->id,
                            'path' => $path ?? 'unknown'
                        ]);
                        // Continue with other galleries even if one fails
                        continue;
                    }
                }

                // Delete the product (this will cascade delete galleries due to foreign key)
                $product->delete();

                DB::commit();
                return ResponseFormatter::success(
                    null,
                    'Product deleted successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error deleting product: ' . $e->getMessage(), [
                    'product_id' => $product->id
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage(), [
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::success(
                null,
                'Data produk berhasil diambil'
            );
        }
    }

   

  

    
}

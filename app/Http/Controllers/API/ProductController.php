<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductGallery;
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

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'merchant_id' => $request->merchant_id,
                'status' => $request->status ?? 'ACTIVE'
            ]);

            return ResponseFormatter::success(
                $product,
                'Product created successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to create product: ' . $e->getMessage(),
                500
            );
        }
    }

    public function all(Request $request)
    {
        $start = microtime(true);
        
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        $cacheDuration = 60; // 60 minutes

        $result = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
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
                    DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                    DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
                ])
                ->when($request->input('id'), fn($q, $id) => $q->where('products.id', $id))
                ->when($request->input('name'), fn($q, $name) => $q->where('products.name', 'like', "%{$name}%"))
                ->when($request->input('description'), fn($q, $desc) => $q->where('products.description', 'like', "%{$desc}%"))
                ->when($request->input('tags'), fn($q, $tags) => $q->where('products.tags', 'like', "%{$tags}%"))
                ->when($request->input('categories'), fn($q, $cat) => $q->where('products.category_id', $cat))
                ->when($request->input('price_from'), fn($q, $price) => $q->where('products.price', '>=', $price))
                ->when($request->input('price_to'), fn($q, $price) => $q->where('products.price', '<=', $price))
                ->orderBy('products.created_at', 'desc');

            $result = $request->input('get_all') 
                ? $query->get() 
                : $query->paginate($request->input('limit', 10));

            // Transform the data to include rating information
            $collection = $request->input('get_all') ? $result : $result->getCollection();
            $collection->transform(function ($product) {
                $product->rating_info = [
                    'average_rating' => round($product->average_rating, 1),
                    'total_reviews' => $product->total_reviews
                ];
                return $product;
            });

            return $result;
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow product query", [
                'duration' => $duration,
                'params' => $request->all()
            ]);
        }

        return ResponseFormatter::success(
            $result,
            'Data produk berhasil diambil'
        );
    }

    public function getByCategory(Request $request, $categoryId)
    {
        $start = microtime(true);

        $cacheKey = "products_category_{$categoryId}_" . md5(json_encode($request->all()));
        $cacheDuration = 60; // 60 minutes

        $products = Cache::remember($cacheKey, $cacheDuration, function () use ($request, $categoryId) {
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
                    'products.created_at',
                    DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                    DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
                ])
                ->where('category_id', $categoryId)
                ->when($request->input('price_from'), fn($q, $price) => $q->where('price', '>=', $price))
                ->when($request->input('price_to'), fn($q, $price) => $q->where('price', '<=', $price))
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('limit', 10));

            if (!$result->isEmpty()) {
                $result->getCollection()->transform(function ($product) {
                    $product->rating_info = [
                        'average_rating' => round($product->average_rating, 1),
                        'total_reviews' => $product->total_reviews
                    ];
                    return $product;
                });
            }

            return $result;
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
            $products,
            $products->isEmpty() ? 'Tidak ada produk yang ditemukan dalam kategori ini' : 'Data produk berhasil diambil'
        );
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
                    DB::raw('AVG(product_reviews.rating) as average_rating'),
                    DB::raw('COUNT(product_reviews.id) as total_reviews')
                ])
                ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                ->with([
                    'merchant:id,name,owner_id', 
                    'category:id,name', 
                    'galleries:id,product_id,url'
                ])
                ->groupBy('products.id')
                ->having('average_rating', '>=', $request->input('min_rating', 4.0))
                ->having('total_reviews', '>=', $request->input('min_reviews', 5))
                ->when($request->input('category_id'), fn($q, $catId) => $q->where('category_id', $catId))
                ->orderBy('average_rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->paginate($request->input('limit', 12));

            if (!$result->isEmpty()) {
                $result->getCollection()->transform(function ($product) {
                    $product->rating_info = [
                        'average_rating' => round($product->average_rating, 1),
                        'total_reviews' => $product->total_reviews
                    ];
                    return $product;
                });
            }

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
                    DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                    DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
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

            // Add rating statistics
            $ratingStats = [
                'average' => round($product->average_rating, 1),
                'total' => $product->total_reviews,
                'distribution' => [
                    5 => $product->reviews()->where('rating', 5)->count(),
                    4 => $product->reviews()->where('rating', 4)->count(),
                    3 => $product->reviews()->where('rating', 3)->count(),
                    2 => $product->reviews()->where('rating', 2)->count(),
                    1 => $product->reviews()->where('rating', 1)->count(),
                ]
            ];

            $product->rating_info = $ratingStats;
            $product->reviews = $reviews;

            return $product;
        });

        $duration = microtime(true) - $start;
        if ($duration > 1) { // Log queries slower than 1 second
            Log::warning("Slow product with reviews query", [
                'duration' => $duration,
                'product_id' => $id
            ]);
        }

        if (!$product) {
            return ResponseFormatter::error(
                null,
                'Produk tidak ditemukan',
                404
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
                        DB::raw('AVG(product_reviews.rating) as average_rating'),
                        DB::raw('COUNT(product_reviews.id) as total_reviews')
                    ])
                    ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                    ->where('category_id', $category->id)
                    ->with([
                        'merchant:id,name,owner_id',
                        'galleries:id,product_id,url'
                    ])
                    ->groupBy('products.id')
                    ->having('average_rating', '>=', $request->input('min_rating', 4.0))
                    ->having('total_reviews', '>=', $request->input('min_reviews', 3))
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('total_reviews', 'desc')
                    ->limit($request->input('limit', 5))
                    ->get();

                if ($topProducts->isNotEmpty()) {
                    $topProducts->transform(function ($product) {
                        $product->rating_info = [
                            'average_rating' => round($product->average_rating, 1),
                            'total_reviews' => $product->total_reviews
                        ];
                        return $product;
                    });

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
                    DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                    DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
                ])
                ->where('merchant_id', $merchantId)
                ->when($request->input('price_from'), fn($q, $price) => $q->where('price', '>=', $price))
                ->when($request->input('price_to'), fn($q, $price) => $q->where('price', '<=', $price))
                ->orderBy('created_at', 'desc');

            $result = $request->input('get_all') 
                ? $query->get() 
                : $query->paginate($request->input('limit', 10));

            // Transform the data to include rating information
            $collection = $request->input('get_all') ? $result : $result->getCollection();
            $collection->transform(function ($product) {
                $product->rating_info = [
                    'average_rating' => round($product->average_rating, 1),
                    'total_reviews' => $product->total_reviews
                ];
                return $product;
            });

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

            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }

            // Check if the authenticated user owns this product through merchant
            if ($product->merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized to update this product',
                    403
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

                // Reload product with variants
                $product->load('variants');

                return ResponseFormatter::success(
                    $product,
                    'Product updated successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update product: ' . $e->getMessage(),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            // Find the product
            $product = Product::with('galleries')->find($id);

            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }

            // Check if the authenticated user owns this product through merchant
            if ($product->merchant->owner_id !== Auth::id()) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized to delete this product',
                    403
                );
            }

            // Delete associated galleries first
            foreach ($product->galleries as $gallery) {
                // Get clean path without storage URL
                $path = str_replace('storage/', '', $gallery->url);

                // Delete the physical file
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            // Delete the product (this will cascade delete galleries due to foreign key)
            $product->delete();

            return ResponseFormatter::success(
                null,
                'Product deleted successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to delete product: ' . $e->getMessage(),
                500
            );
        }
    }

    public function editGallery(Request $request, $productId, $galleryId)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'gallery' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            // Find product and gallery
            $product = Product::find($productId);
            $gallery = ProductGallery::find($galleryId);

            if (!$product || !$gallery) {
                return ResponseFormatter::error(
                    null,
                    'Product or gallery not found',
                    404
                );
            }

            // Delete the old image file
            $oldPath = str_replace('storage/', '', $gallery->url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Store the new file
            $path = $request->file('gallery')->store('product-galleries', 'public');

            // Update gallery record
            $gallery->url = $path;
            $gallery->save();

            return ResponseFormatter::success(
                $gallery,
                'Gallery image updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update gallery image: ' . $e->getMessage(),
                500
            );
        }
    }

    public function deleteGallery($productId, $galleryId)
    {
        try {
            // Find product and gallery
            $product = Product::find($productId);
            $gallery = ProductGallery::find($galleryId);

            if (!$product || !$gallery) {
                return ResponseFormatter::error(
                    null,
                    'Product or gallery not found',
                    404
                );
            }

            // Delete the image file
            $path = str_replace('storage/', '', $gallery->url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Delete the gallery record
            $gallery->delete();

            return ResponseFormatter::success(
                null,
                'Gallery image deleted successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to delete gallery image: ' . $e->getMessage(),
                500
            );
        }
    }

    public function addGallery(Request $request, $id)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'gallery' => 'required|array',
                'gallery.*' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'gallery.required' => 'Please select at least one image to upload',
                'gallery.array' => 'Invalid image format',
                'gallery.*.required' => 'Each uploaded file is required',
                'gallery.*.image' => 'File must be an image',
                'gallery.*.mimes' => 'Image must be jpeg, png, jpg, or gif',
                'gallery.*.max' => 'Image size must not exceed 2MB'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    [
                        'errors' => $validator->errors()->toArray()
                    ],
                    'Validation failed',
                    422
                );
            }

            // Find product
            $product = Product::find($id);
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }

            $galleries = [];

            // Handle the uploaded files
            if ($request->hasFile('gallery')) {
                $files = $request->file('gallery');

                // Ensure $files is always an array
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    try {
                        // Store file
                        $path = $file->store('product-galleries', 'public');

                        // Create gallery record
                        $gallery = $product->galleries()->create([
                            'url' => $path
                        ]);

                        $galleries[] = [
                            'id' => $gallery->id,
                            'url' => asset('storage/' . $path),
                            'product_id' => $product->id
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error processing file: ' . $e->getMessage());
                        continue;
                    }
                }
            }

            if (empty($galleries)) {
                return ResponseFormatter::error(
                    null,
                    'No images were successfully uploaded',
                    400
                );
            }

            return ResponseFormatter::success(
                $galleries,
                'Gallery images uploaded successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to upload gallery images: ' . $e->getMessage(),
                500
            );
        }
    }
}

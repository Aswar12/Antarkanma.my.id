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
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $product = Product::with(['merchant', 'category', 'galleries'])
            ->select(
                'products.*',
                DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
            );

        if ($id) {
            $product->where('id', $id);
        }

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }

        if ($tags) {
            $product->where('tags', 'like', '%' . $tags . '%');
        }

        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }

        if ($categories) {
            $product->where('category_id', $categories);
        }

        $result = $product->paginate($limit);

        // Transform the data to include rating information
        $result->getCollection()->transform(function ($product) {
            $product->rating_info = [
                'average_rating' => round($product->average_rating, 1),
                'total_reviews' => $product->total_reviews
            ];
            return $product;
        });

        return ResponseFormatter::success(
            $result,
            'Data produk berhasil diambil'
        );
    }

    public function getByCategory(Request $request, $categoryId)
    {
        $limit = $request->input('limit', 10);
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $query = Product::with(['merchant', 'category', 'galleries'])
            ->select(
                'products.*',
                DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
            )
            ->where('category_id', $categoryId);

        // Filter by price range if provided
        if ($price_from) {
            $query->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $query->where('price', '<=', $price_to);
        }

        // Order by created_at by default
        $query->orderBy('created_at', 'desc');

        $products = $query->paginate($limit);

        if ($products->isEmpty()) {
            return ResponseFormatter::success(
                $products,
                'Tidak ada produk yang ditemukan dalam kategori ini'
            );
        }

        // Transform the data to include rating information
        $products->getCollection()->transform(function ($product) {
            $product->rating_info = [
                'average_rating' => round($product->average_rating, 1),
                'total_reviews' => $product->total_reviews
            ];
            return $product;
        });

        return ResponseFormatter::success(
            $products,
            'Data produk berhasil diambil'
        );
    }

    public function getPopularProducts(Request $request)
    {
        $limit = $request->input('limit', 12);
        $category_id = $request->input('category_id');
        $min_rating = $request->input('min_rating', 4.0);
        $min_reviews = $request->input('min_reviews', 5);

        $query = Product::select(
            'products.*',
            DB::raw('AVG(product_reviews.rating) as average_rating'),
            DB::raw('COUNT(product_reviews.id) as total_reviews')
        )
            ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            ->with(['merchant', 'category', 'galleries'])
            ->groupBy('products.id')
            ->having('average_rating', '>=', $min_rating)
            ->having('total_reviews', '>=', $min_reviews)
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_reviews', 'desc');

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $products = $query->paginate($limit);

        if ($products->isEmpty()) {
            return ResponseFormatter::success(
                $products,
                'Tidak ada produk populer yang ditemukan'
            );
        }

        // Transform the data to include rating information
        $products->getCollection()->transform(function ($product) {
            $product->rating_info = [
                'average_rating' => round($product->average_rating, 1),
                'total_reviews' => $product->total_reviews
            ];
            return $product;
        });

        return ResponseFormatter::success(
            $products,
            'Data produk populer berhasil diambil'
        );
    }

    public function getProductWithReviews($id)
    {
        $product = Product::with(['merchant', 'category', 'galleries'])
            ->select(
                'products.*',
                DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
            )
            ->where('id', $id)
            ->first();

        if (!$product) {
            return ResponseFormatter::error(
                null,
                'Produk tidak ditemukan',
                404
            );
        }

        // Get reviews
        $reviews = $product->reviews()
            ->with('user:id,name')
            ->select('id', 'user_id', 'rating', 'comment', 'created_at')
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

        return ResponseFormatter::success(
            $product,
            'Data produk dan review berhasil diambil'
        );
    }

    public function getTopProductsByCategory(Request $request)
    {
        $limit = $request->input('limit', 5);
        $min_rating = $request->input('min_rating', 4.0);
        $min_reviews = $request->input('min_reviews', 3);

        $categories = ProductCategory::all();
        $result = [];

        foreach ($categories as $category) {
            $topProducts = Product::select(
                'products.*',
                DB::raw('AVG(product_reviews.rating) as average_rating'),
                DB::raw('COUNT(product_reviews.id) as total_reviews')
            )
                ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                ->where('category_id', $category->id)
                ->with(['merchant', 'galleries'])
                ->groupBy('products.id')
                ->having('average_rating', '>=', $min_rating)
                ->having('total_reviews', '>=', $min_reviews)
                ->orderBy('average_rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->limit($limit)
                ->get();

            if ($topProducts->isNotEmpty()) {
                // Transform products to include rating information
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

        return ResponseFormatter::success(
            $result,
            'Data produk top per kategori berhasil diambil'
        );
    }

    public function getProductByMerchant(Request $request, $merchantId)
    {
        $limit = $request->input('limit', 10);
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $query = Product::with(['merchant', 'category', 'galleries'])
            ->select(
                'products.*',
                DB::raw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id) as average_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id) as total_reviews')
            )
            ->where('merchant_id', $merchantId);

        // Filter by price range if provided
        if ($price_from) {
            $query->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $query->where('price', '<=', $price_to);
        }

        // Order by created_at by default
        $query->orderBy('created_at', 'desc');

        $products = $query->paginate($limit);

        if ($products->isEmpty()) {
            return ResponseFormatter::success(
                $products,
                'Tidak ada produk ditemukan untuk merchant ini'
            );
        }

        // Transform the data to include rating information
        $products->getCollection()->transform(function ($product) {
            $product->rating_info = [
                'average_rating' => round($product->average_rating, 1),
                'total_reviews' => $product->total_reviews
            ];
            return $product;
        });

        return ResponseFormatter::success(
            $products,
            'Data produk berhasil diambil'
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
                'merchant_id' => 'exists:merchants,id',
                'status' => 'in:ACTIVE,INACTIVE,OUT_OF_STOCK'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            // Update product
            $product->update($request->all());

            return ResponseFormatter::success(
                $product,
                'Product updated successfully'
            );
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
            $path = $request->file('gallery')->store('product_galleries', 'public');

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
                        $path = $file->store('product_galleries', 'public');
                        
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

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

class ProductController extends Controller
{
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

    // ... (previous methods remain unchanged)

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
        $limit = $request->input('limit', 10);
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

    // ... (rest of the existing methods remain unchanged)
}

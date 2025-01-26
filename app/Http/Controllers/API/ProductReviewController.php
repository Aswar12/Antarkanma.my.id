<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    public function getByProduct($productId, Request $request)
    {
        $limit = $request->input('limit', 10);
        $rating = $request->input('rating');

        $query = ProductReview::with(['user:id,name,profile_photo_path'])
            ->select(
                'product_reviews.*',
                DB::raw('(SELECT COUNT(*) FROM product_reviews pr2 WHERE pr2.rating = product_reviews.rating AND pr2.product_id = product_reviews.product_id) as rating_count')
            )
            ->where('product_id', $productId);

        if ($rating) {
            $query->where('rating', $rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        // Get rating statistics
        $stats = [
            'average_rating' => ProductReview::where('product_id', $productId)->avg('rating'),
            'total_reviews' => ProductReview::where('product_id', $productId)->count(),
            'rating_distribution' => ProductReview::where('product_id', $productId)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];

        return ResponseFormatter::success([
            'reviews' => $reviews,
            'stats' => $stats
        ], 'Data review berhasil diambil');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                null,
                $validator->errors()->first(),
                422
            );
        }

        // Check if user already reviewed this product
        $existingReview = ProductReview::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return ResponseFormatter::error(
                null,
                'Anda sudah memberikan review untuk produk ini',
                422
            );
        }

        $review = ProductReview::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return ResponseFormatter::success(
            $review->load('user:id,name,profile_photo_path'),
            'Review berhasil ditambahkan'
        );
    }

    public function update(Request $request, $id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return ResponseFormatter::error(
                null,
                'Review tidak ditemukan',
                404
            );
        }

        if ($review->user_id !== Auth::id()) {
            return ResponseFormatter::error(
                null,
                'Anda tidak memiliki akses untuk mengubah review ini',
                403
            );
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                null,
                $validator->errors()->first(),
                422
            );
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return ResponseFormatter::success(
            $review->load('user:id,name,profile_photo_path'),
            'Review berhasil diperbarui'
        );
    }

    public function destroy($id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return ResponseFormatter::error(
                null,
                'Review tidak ditemukan',
                404
            );
        }

        if ($review->user_id !== Auth::id()) {
            return ResponseFormatter::error(
                null,
                'Anda tidak memiliki akses untuk menghapus review ini',
                403
            );
        }

        $review->delete();

        return ResponseFormatter::success(
            null,
            'Review berhasil dihapus'
        );
    }

    public function getUserReviews()
    {
        $reviews = ProductReview::with(['product:id,name,price'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseFormatter::success(
            $reviews,
            'Data review berhasil diambil'
        );
    }
}

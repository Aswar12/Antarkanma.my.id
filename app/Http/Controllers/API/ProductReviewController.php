<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $product_id = $request->input('product_id');
        $limit = $request->input('limit', 10);

        $productReviews = ProductReview::with(['user'])
            ->where('product_id', $product_id)
            ->paginate($limit);

        return ResponseFormatter::success(
            $productReviews,
            'Data product reviews berhasil diambil'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required'
        ]);

        try {
            $review = ProductReview::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return ResponseFormatter::success(
                $review,
                'Review berhasil ditambahkan'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                $error->getMessage(),
                'Gagal menambahkan review'
            );
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required'
        ]);

        try {
            $review = ProductReview::findOrFail($id);

            if ($review->user_id !== Auth::id()) {
                return ResponseFormatter::error(
                    'Unauthorized',
                    'Anda tidak memiliki izin untuk mengubah review ini',
                    403
                );
            }

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return ResponseFormatter::success(
                $review,
                'Review berhasil diperbarui'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                $error->getMessage(),
                'Gagal memperbarui review'
            );
        }
    }

    public function destroy($id)
    {
        try {
            $review = ProductReview::findOrFail($id);

            if ($review->user_id !== Auth::id()) {
                return ResponseFormatter::error(
                    'Unauthorized',
                    'Anda tidak memiliki izin untuk menghapus review ini',
                    403
                );
            }

            $review->delete();

            return ResponseFormatter::success(
                null,
                'Review berhasil dihapus'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                $error->getMessage(),
                'Gagal menghapus review'
            );
        }
    }
}

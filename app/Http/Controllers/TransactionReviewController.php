<?php

namespace App\Http\Controllers;

use App\Models\MerchantReview;
use App\Models\CourierReview;
use App\Models\ProductReview;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransactionReviewController extends Controller
{
    /**
     * Submit reviews for a completed transaction
     * Expects:
     * - courier_review: { rating, note }
     * - merchant_reviews: [{ merchant_id, order_id, rating, comment }]
     * - product_reviews: [{ product_id, rating, comment }]
     */
    public function submitReview(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'courier_review' => 'nullable|array',
            'courier_review.rating' => 'required_with:courier_review|integer|between:1,5',
            'courier_review.note' => 'nullable|string|max:500',
            
            'merchant_reviews' => 'nullable|array',
            'merchant_reviews.*.merchant_id' => 'required_with:merchant_reviews|exists:merchants,id',
            'merchant_reviews.*.order_id' => 'required_with:merchant_reviews|exists:orders,id',
            'merchant_reviews.*.rating' => 'required_with:merchant_reviews|integer|between:1,5',
            'merchant_reviews.*.comment' => 'nullable|string|max:500',
            
            'product_reviews' => 'nullable|array',
            'product_reviews.*.product_id' => 'required_with:product_reviews|exists:products,id',
            'product_reviews.*.rating' => 'required_with:product_reviews|integer|between:1,5',
            'product_reviews.*.comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                null,
                $validator->errors()->first(),
                422
            );
        }

        // Verify transaction exists and belongs to user
        $transaction = Transaction::where('id', $transactionId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$transaction) {
            return ResponseFormatter::error(
                null,
                'Transaksi tidak ditemukan',
                404
            );
        }

        // Check if transaction is completed
        if ($transaction->status !== Transaction::STATUS_COMPLETED) {
            return ResponseFormatter::error(
                null,
                'Hanya dapat memberikan review untuk transaksi yang sudah selesai',
                422
            );
        }

        DB::transaction(function () use ($request, $transaction) {
            // Submit courier review
            if ($request->has('courier_review') && $transaction->courier_id) {
                $courierReviewData = $request->input('courier_review');
                
                // Check if user already reviewed this courier for this transaction
                $existingCourierReview = CourierReview::where('user_id', Auth::id())
                    ->where('courier_id', $transaction->courier_id)
                    ->where('transaction_id', $transaction->id)
                    ->first();

                if (!$existingCourierReview) {
                    CourierReview::create([
                        'user_id' => Auth::id(),
                        'courier_id' => $transaction->courier_id,
                        'transaction_id' => $transaction->id,
                        'rating' => $courierReviewData['rating'],
                        'note' => $courierReviewData['note'] ?? null,
                    ]);
                }
            }

            // Submit merchant reviews
            if ($request->has('merchant_reviews')) {
                foreach ($request->input('merchant_reviews') as $merchantReviewData) {
                    // Verify order belongs to this transaction
                    $order = Order::where('id', $merchantReviewData['order_id'])
                        ->where('transaction_id', $transaction->id)
                        ->first();

                    if ($order) {
                        // Check if user already reviewed this merchant for this order
                        $existingMerchantReview = MerchantReview::where('user_id', Auth::id())
                            ->where('merchant_id', $merchantReviewData['merchant_id'])
                            ->where('order_id', $merchantReviewData['order_id'])
                            ->first();

                        if (!$existingMerchantReview) {
                            MerchantReview::create([
                                'user_id' => Auth::id(),
                                'merchant_id' => $merchantReviewData['merchant_id'],
                                'order_id' => $merchantReviewData['order_id'],
                                'transaction_id' => $transaction->id,
                                'rating' => $merchantReviewData['rating'],
                                'comment' => $merchantReviewData['comment'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Submit product reviews
            if ($request->has('product_reviews')) {
                foreach ($request->input('product_reviews') as $productReviewData) {
                    // Check if user already reviewed this product
                    $existingProductReview = ProductReview::where('user_id', Auth::id())
                        ->where('product_id', $productReviewData['product_id'])
                        ->first();

                    if (!$existingProductReview) {
                        ProductReview::create([
                            'user_id' => Auth::id(),
                            'product_id' => $productReviewData['product_id'],
                            'rating' => $productReviewData['rating'],
                            'comment' => $productReviewData['comment'] ?? null,
                        ]);
                    }
                }
            }
        });

        return ResponseFormatter::success(
            null,
            'Terima kasih atas review Anda!'
        );
    }

    /**
     * Get review status for a transaction
     * Returns which reviews have been submitted
     */
    public function getReviewStatus($transactionId)
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$transaction) {
            return ResponseFormatter::error(
                null,
                'Transaksi tidak ditemukan',
                404
            );
        }

        $status = [
            'transaction_id' => $transactionId,
            'courier_review_submitted' => false,
            'merchant_review_ids' => [],
            'product_review_ids' => [],
        ];

        // Check courier review
        if ($transaction->courier_id) {
            $courierReview = CourierReview::where('user_id', Auth::id())
                ->where('courier_id', $transaction->courier_id)
                ->where('transaction_id', $transactionId)
                ->first();

            $status['courier_review_submitted'] = $courierReview !== null;
        }

        // Check merchant reviews
        $merchantReviews = MerchantReview::where('user_id', Auth::id())
            ->where('transaction_id', $transactionId)
            ->get();

        $status['merchant_review_ids'] = $merchantReviews->pluck('id')->toArray();

        // Check product reviews
        $productReviews = ProductReview::where('user_id', Auth::id())
            ->get();

        $status['product_review_ids'] = $productReviews->pluck('id')->toArray();

        return ResponseFormatter::success(
            $status,
            'Status review berhasil diambil'
        );
    }

    /**
     * Get all reviews for a merchant
     */
    public function getMerchantReviews($merchantId, Request $request)
    {
        $limit = $request->input('limit', 10);
        $rating = $request->input('rating');

        $query = MerchantReview::with(['user:id,name,profile_photo_path'])
            ->select(
                'merchant_reviews.*',
                DB::raw('(SELECT COUNT(*) FROM merchant_reviews mr2 WHERE mr2.rating = merchant_reviews.rating AND mr2.merchant_id = merchant_reviews.merchant_id) as rating_count')
            )
            ->where('merchant_id', $merchantId);

        if ($rating) {
            $query->where('rating', $rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        // Get rating statistics
        $stats = [
            'average_rating' => MerchantReview::where('merchant_id', $merchantId)->avg('rating'),
            'total_reviews' => MerchantReview::where('merchant_id', $merchantId)->count(),
            'rating_distribution' => MerchantReview::where('merchant_id', $merchantId)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];

        return ResponseFormatter::success([
            'reviews' => $reviews,
            'stats' => $stats
        ], 'Data review merchant berhasil diambil');
    }

    /**
     * Get all reviews for a courier
     */
    public function getCourierReviews($courierId, Request $request)
    {
        $limit = $request->input('limit', 10);

        $reviews = CourierReview::with(['user:id,name,profile_photo_path'])
            ->where('courier_id', $courierId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        // Get rating statistics
        $stats = [
            'average_rating' => CourierReview::where('courier_id', $courierId)->avg('rating'),
            'total_reviews' => CourierReview::where('courier_id', $courierId)->count(),
            'rating_distribution' => CourierReview::where('courier_id', $courierId)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];

        return ResponseFormatter::success([
            'reviews' => $reviews,
            'stats' => $stats
        ], 'Data review kurir berhasil diambil');
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist with product details
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $wishlists = Wishlist::where('user_id', $user->id)
                ->with(['product' => function ($query) {
                    $query->with(['galleries', 'merchant', 'category', 'variants', 'reviews']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Filter out any entries where product was deleted
            $products = $wishlists
                ->filter(fn($w) => $w->product !== null)
                ->map(function ($wishlist) {
                    $product = $wishlist->product;
                    $product->wishlisted_at = $wishlist->created_at;
                    return $product;
                })
                ->values();

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Wishlist fetched successfully',
                ],
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Failed to fetch wishlist: ' . $e->getMessage(),
                ],
                'data' => null,
            ], 500);
        }
    }

    /**
     * Toggle product in wishlist (add if not exists, remove if exists)
     */
    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
            ]);

            $user = $request->user();
            $productId = $request->product_id;

            $existing = Wishlist::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                $existing->delete();
                $isWishlisted = false;
                $message = 'Product removed from wishlist';
            } else {
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $isWishlisted = true;
                $message = 'Product added to wishlist';
            }

            // Get updated count
            $wishlistCount = Wishlist::where('user_id', $user->id)->count();

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => $message,
                ],
                'data' => [
                    'product_id' => $productId,
                    'is_wishlisted' => $isWishlisted,
                    'wishlist_count' => $wishlistCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Failed to toggle wishlist: ' . $e->getMessage(),
                ],
                'data' => null,
            ], 500);
        }
    }

    /**
     * Batch check which products are in user's wishlist
     */
    public function check(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'integer',
            ]);

            $user = $request->user();
            $productIds = $request->product_ids;

            $wishlisted = Wishlist::where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Wishlist check completed',
                ],
                'data' => [
                    'wishlisted_product_ids' => $wishlisted,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Failed to check wishlist: ' . $e->getMessage(),
                ],
                'data' => null,
            ], 500);
        }
    }
}

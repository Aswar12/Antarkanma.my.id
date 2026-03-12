<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartSync;
use App\Models\Product;
use App\Models\Merchant;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

class CartSyncController extends Controller
{
    /**
     * Display a listing of the user's synced cart.
     */
    public function getCart(Request $request)
    {
        try {
            $user = Auth::user();
            
            $cartItems = CartSync::with([
                'product',
                'product.merchant',
                'product.category',
                'product.galleries',
                'variant',
                'merchant'
            ])
            ->forUser($user->id)
            ->orderBy('last_added_at', 'desc')
            ->get()
            ->groupBy('merchant_id');

            $formattedCart = [];
            $totalAmount = 0;
            $totalItems = 0;

            foreach ($cartItems as $merchantId => $items) {
                $merchant = $items->first()->merchant;
                
                $formattedItems = $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'description' => $item->product->description,
                            'status' => $item->product->status,
                            'image' => $item->product->galleries->first()?->image_url,
                            'category' => [
                                'id' => $item->product->category->id,
                                'name' => $item->product->category->name,
                            ],
                        ],
                        'variant' => $item->variant ? [
                            'id' => $item->variant->id,
                            'name' => $item->variant->name,
                            'price' => $item->variant->price,
                        ] : null,
                        'quantity' => $item->quantity,
                        'is_selected' => $item->is_selected,
                        'total_price' => $item->variant 
                            ? ($item->product->price + $item->variant->price) * $item->quantity
                            : $item->product->price * $item->quantity,
                        'last_added_at' => $item->last_added_at->toIso8601String(),
                    ];
                })->toArray();

                $selectedItems = array_filter($formattedItems, fn($item) => $item['is_selected']);
                $merchantTotal = array_sum(array_column($selectedItems, 'total_price'));
                $totalAmount += $merchantTotal;
                $totalItems += count($selectedItems);

                $formattedCart[] = [
                    'merchant_id' => $merchantId,
                    'merchant_name' => $merchant->name,
                    'merchant_logo' => $merchant->logo,
                    'merchant_is_active' => $merchant->is_active,
                    'items' => $formattedItems,
                    'selected_items_count' => count($selectedItems),
                    'total_amount' => $merchantTotal,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'cart' => $formattedCart,
                    'summary' => [
                        'total_merchants' => count($formattedCart),
                        'total_items' => $totalItems,
                        'total_amount' => $totalAmount,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync cart items from client to server.
     */
    public function syncCart(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'cart' => 'required|array',
                'cart.*.merchant_id' => 'required|exists:merchants,id',
                'cart.*.items' => 'required|array',
                'cart.*.items.*.product_id' => 'required|exists:products,id',
                'cart.*.items.*.variant_id' => 'nullable|exists:product_variants,id',
                'cart.*.items.*.quantity' => 'required|integer|min:1|max:99',
                'cart.*.items.*.is_selected' => 'boolean',
            ]);

            DB::beginTransaction();

            foreach ($request->cart as $merchantData) {
                $merchantId = $merchantData['merchant_id'];

                foreach ($merchantData['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    
                    // Verify product belongs to merchant
                    if ($product->merchant_id !== $merchantId) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Product does not belong to specified merchant',
                        ], 422);
                    }

                    // Check if variant belongs to product
                    if (isset($itemData['variant_id'])) {
                        $variant = ProductVariant::findOrFail($itemData['variant_id']);
                        if ($variant->product_id !== $itemData['product_id']) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Variant does not belong to specified product',
                            ], 422);
                        }
                    }

                    // Find existing cart item or create new
                    $cartItem = CartSync::where('user_id', $user->id)
                        ->where('product_id', $itemData['product_id'])
                        ->where('merchant_id', $merchantId)
                        ->whereNull('product_variant_id')
                        ->when(isset($itemData['variant_id']), function ($query) use ($itemData) {
                            return $query->where('product_variant_id', $itemData['variant_id']);
                        })
                        ->first();

                    if ($cartItem) {
                        // Update existing item
                        $cartItem->update([
                            'quantity' => $itemData['quantity'],
                            'is_selected' => $itemData['is_selected'] ?? true,
                            'last_added_at' => now(),
                        ]);
                    } else {
                        // Create new item
                        CartSync::create([
                            'user_id' => $user->id,
                            'product_id' => $itemData['product_id'],
                            'merchant_id' => $merchantId,
                            'product_variant_id' => $itemData['variant_id'] ?? null,
                            'quantity' => $itemData['quantity'],
                            'is_selected' => $itemData['is_selected'] ?? true,
                            'last_added_at' => now(),
                            'has_checked_out' => false,
                        ]);
                    }
                }
            }

            DB::commit();

            // Return updated cart
            return $this->getCart($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a specific cart item.
     */
    public function updateItem(Request $request, $itemId)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'quantity' => 'sometimes|integer|min:1|max:99',
                'is_selected' => 'boolean',
            ]);

            $cartItem = CartSync::where('id', $itemId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $updateData = [];
            
            if ($request->has('quantity')) {
                $updateData['quantity'] = $request->quantity;
            }
            
            if ($request->has('is_selected')) {
                $updateData['is_selected'] = $request->is_selected;
            }

            if (!empty($updateData)) {
                $updateData['last_added_at'] = now();
                $cartItem->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'is_selected' => $cartItem->is_selected,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove item(s) from cart.
     */
    public function removeFromCart(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'required|integer|exists:cart_syncs,id',
            ]);

            $deletedCount = CartSync::where('user_id', $user->id)
                ->whereIn('id', $request->item_ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart items removed successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear entire cart.
     */
    public function clearCart(Request $request)
    {
        try {
            $user = Auth::user();
            
            $deletedCount = CartSync::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark cart items as checked out.
     */
    public function markAsCheckedOut(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'merchant_ids' => 'sometimes|array',
                'merchant_ids.*' => 'required|integer|exists:merchants,id',
            ]);

            $query = CartSync::where('user_id', $user->id)
                ->where('is_selected', true);

            if ($request->has('merchant_ids')) {
                $query->whereIn('merchant_id', $request->merchant_ids);
            }

            $updatedCount = $query->update([
                'has_checked_out' => true,
                'last_checkout_attempt_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart marked as checked out successfully',
                'data' => [
                    'updated_count' => $updatedCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark cart as checked out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cart analytics for admin (abandoned carts).
     */
    public function getCartAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', '7_days');
            $limit = $request->get('limit', 20);

            // Calculate date range
            $startDate = match ($period) {
                'today' => now()->startOfDay(),
                '7_days' => now()->subDays(7)->startOfDay(),
                '30_days' => now()->subDays(30)->startOfDay(),
                'all_time' => now()->subYears(10)->startOfDay(),
                default => now()->subDays(7)->startOfDay(),
            };

            // Get products most added to cart but not checked out
            $abandonedProducts = CartSync::with([
                'product',
                'product.merchant',
                'user'
            ])
            ->where('has_checked_out', false)
            ->where('last_added_at', '>=', $startDate)
            ->selectRaw('product_id, COUNT(*) as times_added, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('times_added')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_price' => $item->product->price,
                    'product_image' => $item->product->galleries->first()?->image_url,
                    'merchant_id' => $item->product->merchant_id,
                    'merchant_name' => $item->product->merchant->name,
                    'times_added' => $item->times_added,
                    'total_quantity' => $item->total_quantity,
                    'estimated_revenue_lost' => $item->product->price * $item->total_quantity,
                    'last_added_at' => $item->last_added_at->toIso8601String(),
                ];
            });

            // Get summary statistics
            $summary = [
                'total_abandoned_items' => CartSync::where('has_checked_out', false)
                    ->where('last_added_at', '>=', $startDate)
                    ->count(),
                'total_abandoned_value' => CartSync::where('has_checked_out', false)
                    ->where('last_added_at', '>=', $startDate)
                    ->with('product')
                    ->get()
                    ->sum(fn($item) => $item->product->price * $item->quantity),
                'unique_users_with_abandoned_cart' => CartSync::where('has_checked_out', false)
                    ->where('last_added_at', '>=', $startDate)
                    ->distinct('user_id')
                    ->count('user_id'),
                'period' => $period,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Cart analytics retrieved successfully',
                'data' => [
                    'summary' => $summary,
                    'abandoned_products' => $abandonedProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

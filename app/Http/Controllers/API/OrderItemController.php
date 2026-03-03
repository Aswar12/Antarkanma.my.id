<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrderItemController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'customer_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $order = Order::findOrFail($request->order_id);
            
            // Check authorization
            if ($order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to add items to this order', 403);
            }
            
            // Check order status - only PENDING and WAITING_APPROVAL orders can be modified
            if (!in_array($order->order_status, ['PENDING', 'WAITING_APPROVAL'])) {
                return ResponseFormatter::error(
                    'Cannot add items to order with status: ' . $order->order_status . '. Order must be in PENDING or WAITING_APPROVAL status.',
                    400
                );
            }
            
            // Check product stock
            $product = Product::findOrFail($request->product_id);
            if ($product->stock < $request->quantity) {
                return ResponseFormatter::error(
                    'Insufficient stock. Available: ' . $product->stock . ', Requested: ' . $request->quantity,
                    400
                );
            }
            
            // Determine price (variant price or product price)
            $price = $product->price;
            if ($request->product_variant_id) {
                $variant = ProductVariant::findOrFail($request->product_variant_id);
                $price = $variant->price;
            }
            
            // Create order item
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $request->product_variant_id,
                'merchant_id' => $product->merchant_id,
                'quantity' => $request->quantity,
                'price' => $price,
                'customer_note' => $request->customer_note,
            ]);
            
            // Recalculate order total
            $order->recalculateTotal();
            
            return ResponseFormatter::success($orderItem, 'Order item created successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to create order item: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            if ($orderItem->order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to view this order item', 403);
            }
            return ResponseFormatter::success($orderItem, 'Order item retrieved successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Order item not found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $orderItem = OrderItem::findOrFail($id);
            $order = $orderItem->order;
            
            // Check authorization
            if ($order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to update this order item', 403);
            }
            
            // Check order status
            if (!in_array($order->order_status, ['PENDING', 'WAITING_APPROVAL'])) {
                return ResponseFormatter::error(
                    'Cannot update items in order with status: ' . $order->order_status,
                    400
                );
            }
            
            // Check stock if quantity increased
            if ($request->quantity > $orderItem->quantity) {
                $product = $orderItem->product;
                $availableStock = $product->stock + $orderItem->quantity; // Add back current item stock
                
                if ($availableStock < $request->quantity) {
                    return ResponseFormatter::error(
                        'Insufficient stock. Available: ' . $product->stock,
                        400
                    );
                }
            }
            
            $orderItem->update($validator->validated());
            
            // Recalculate order total
            $order->recalculateTotal();
            
            return ResponseFormatter::success($orderItem, 'Order item updated successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to update order item: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            if ($orderItem->order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to delete this order item', 403);
            }

            $orderItem->delete();
            return ResponseFormatter::success(null, 'Order item deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to delete order item: ' . $e->getMessage(), 500);
        }
    }

    public function list($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            if ($order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to view items for this order', 403);
            }

            $orderItems = $order->orderItems;
            return ResponseFormatter::success($orderItems, 'Order items list retrieved successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to retrieve order items: ' . $e->getMessage(), 500);
        }
    }
}

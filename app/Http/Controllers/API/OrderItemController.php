<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\OrderItem;
use App\Models\Order;
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
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $order = Order::findOrFail($request->order_id);
            if ($order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to add items to this order', 403);
            }

            $orderItem = OrderItem::create($validator->validated());
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
            'quantity' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $orderItem = OrderItem::findOrFail($id);
            if ($orderItem->order->user_id !== Auth::id()) {
                return ResponseFormatter::error('You are not authorized to update this order item', 403);
            }

            $orderItem->update($validator->validated());
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

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_location_id' => 'required|exists:user_locations,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:MANUAL,ONLINE',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);
            $userLocation = UserLocation::findOrFail($request->user_location_id);

            // Create Order
            $order = new Order();
            $order->user_id = $user->id;
            $order->order_status = 'PENDING';
            $order->save();

            $totalAmount = 0;

            // Create Order Items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $product->id;
                $orderItem->merchant_id = $product->merchant_id;
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $product->price;
                $orderItem->save();

                $totalAmount += $product->price * $item['quantity'];
            }

            // Update Order total amount
            $order->total_amount = $totalAmount;
            $order->save();

            // Create Transaction
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->user_id = $user->id;
            $transaction->user_location_id = $userLocation->id;
            $transaction->total_price = $totalAmount;
            $transaction->shipping_price = 0; // You might want to calculate this based on distance or other factors
            $transaction->status = 'PENDING';
            $transaction->payment_method = $request->payment_method;
            $transaction->payment_status = 'PENDING';
            $transaction->save();

            DB::commit();

            return ResponseFormatter::success([
                'order' => $order->load('items', 'transaction'),
            ], 'Order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Order creation failed: ' . $e->getMessage(), 500);
        }
    }
}

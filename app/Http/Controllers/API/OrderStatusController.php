<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;

class OrderStatusController extends Controller
{
    /**
     * Update order status to PROCESSING
     */
    public function processOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction', 'orderItems.product.merchant'])->findOrFail($id);
            
            // Validate current status
            if ($order->order_status !== 'PENDING') {
                return ResponseFormatter::error(
                    null,
                    'Only pending orders can be processed',
                    422
                );
            }

            // For online payment, validate payment status
            // For COD (MANUAL), allow processing without payment
            if ($order->transaction->payment_method === 'ONLINE' && 
                $order->transaction->payment_status !== 'COMPLETED') {
                return ResponseFormatter::error(
                    null,
                    'Payment must be completed for online orders',
                    422
                );
            }

            // Update order status
            $order->order_status = 'PROCESSING';
            $order->save();

            // Transaction status remains PENDING
            // For COD, payment will be completed upon delivery
            
            // Send notification to customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'MANUAL' 
                    ? 'Your COD order is being processed'
                    : 'Your order is being processed';

                $firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'action' => 'order_processing',
                        'order_id' => $order->id,
                        'payment_method' => $order->transaction->payment_method
                    ],
                    'Order Processing',
                    $message
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction', 'orderItems.product.merchant']),
                'Order is now being processed'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to process order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark order as ready for pickup
     */
    public function readyForPickup($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction', 'orderItems.product.merchant'])->findOrFail($id);
            
            // Validate current status
            if ($order->order_status !== 'PROCESSING') {
                return ResponseFormatter::error(
                    null,
                    'Only processing orders can be marked as ready',
                    422
                );
            }

            // Update order status
            $order->order_status = 'READY_FOR_PICKUP';
            $order->save();

            // Create delivery record
            $delivery = new Delivery([
                'transaction_id' => $order->transaction->id,
                'delivery_status' => 'PENDING',
                'estimated_delivery_time' => now()->addHours(1)
            ]);
            $delivery->save();

            // Create delivery items
            foreach ($order->orderItems as $item) {
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'order_item_id' => $item->id,
                    'pickup_status' => 'PENDING'
                ]);
            }

            // Notify couriers with payment method info
            $firebaseService = new FirebaseService();
            $courierTokens = User::where('roles', 'COURIER')
                ->with('fcmTokens')
                ->get()
                ->pluck('fcmTokens')
                ->flatten()
                ->pluck('token')
                ->toArray();

            if (!empty($courierTokens)) {
                $merchant = $order->orderItems->first()->product->merchant;
                $isCOD = $order->transaction->payment_method === 'MANUAL';
                
                $firebaseService->sendToUser(
                    $courierTokens,
                    [
                        'action' => 'order_ready_pickup',
                        'order_id' => $order->id,
                        'is_cod' => $isCOD,
                        'total_amount' => $isCOD ? $order->transaction->total_price : 0,
                        'merchant' => [
                            'id' => $merchant->id,
                            'name' => $merchant->name,
                            'address' => $merchant->address
                        ]
                    ],
                    'New Order Ready for Pickup',
                    $isCOD 
                        ? "COD Order #{$order->id} ready for pickup at {$merchant->name}. Amount to collect: {$order->transaction->total_price}"
                        : "Order #{$order->id} ready for pickup at {$merchant->name}"
                );
            }

            // Notify customer
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'action' => 'order_ready_pickup',
                        'order_id' => $order->id
                    ],
                    'Order Ready for Pickup',
                    'Your order is ready for pickup by courier'
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction', 'orderItems.product.merchant', 'delivery']),
                'Order is ready for pickup'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to mark order as ready: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Complete the order (after delivery)
     */
    public function complete($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction', 'delivery'])->findOrFail($id);
            
            // Validate current status
            if ($order->order_status !== 'READY_FOR_PICKUP') {
                return ResponseFormatter::error(
                    null,
                    'Only orders in ready for pickup status can be completed',
                    422
                );
            }

            // Update order status
            $order->order_status = 'COMPLETED';
            $order->save();

            // Update transaction status
            $order->transaction->status = 'COMPLETED';
            
            // For COD orders, mark payment as completed upon delivery
            if ($order->transaction->payment_method === 'MANUAL') {
                $order->transaction->payment_status = 'COMPLETED';
                $order->transaction->payment_date = now();
            }
            
            $order->transaction->save();

            // Update delivery status
            if ($order->delivery) {
                $order->delivery->delivery_status = 'DELIVERED';
                $order->delivery->actual_delivery_time = now();
                $order->delivery->save();
            }

            // Notify customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'MANUAL'
                    ? 'Your COD order has been delivered and payment received'
                    : 'Your order has been delivered';

                $firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'action' => 'order_completed',
                        'order_id' => $order->id,
                        'payment_method' => $order->transaction->payment_method
                    ],
                    'Order Completed',
                    $message
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction', 'delivery']),
                'Order completed successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to complete order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel the order
     */
    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction', 'delivery'])->findOrFail($id);
            
            // Validate if order can be cancelled
            if (!in_array($order->order_status, ['PENDING', 'PROCESSING'])) {
                return ResponseFormatter::error(
                    null,
                    'Only pending or processing orders can be cancelled',
                    422
                );
            }

            // Update order status
            $order->order_status = 'CANCELED';
            $order->save();

            // Update transaction status
            $order->transaction->status = 'CANCELED';
            
            // For online payment, handle refund if payment was completed
            if ($order->transaction->payment_method === 'ONLINE' && 
                $order->transaction->payment_status === 'COMPLETED') {
                // Handle refund logic here if needed
            }
            
            $order->transaction->save();

            // Update delivery status if exists
            if ($order->delivery) {
                $order->delivery->delivery_status = 'CANCELED';
                $order->delivery->save();
            }

            // Notify customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'ONLINE' && 
                          $order->transaction->payment_status === 'COMPLETED'
                    ? 'Your order has been canceled. Refund will be processed.'
                    : 'Your order has been canceled';

                $firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'action' => 'order_canceled',
                        'order_id' => $order->id,
                        'refund_needed' => $order->transaction->payment_method === 'ONLINE' && 
                                         $order->transaction->payment_status === 'COMPLETED'
                    ],
                    'Order Canceled',
                    $message
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction', 'delivery']),
                'Order canceled successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to cancel order: ' . $e->getMessage(), 500);
        }
    }
}

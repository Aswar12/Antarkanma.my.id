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
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    /**
     * Update order status to PROCESSING
     */
    public function processOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with([
                'transaction.user.fcmTokens',
                'orderItems.product.merchant',
                'transaction.courier.user.fcmTokens'
            ])->findOrFail($id);

            // Validate current status
            if ($order->order_status !== Order::STATUS_PENDING) {
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
            $order->order_status = Order::STATUS_PROCESSING;
            $order->save();

            // Transaction status remains PENDING
            // For COD, payment will be completed upon delivery

            // Send notification to customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'MANUAL'
                    ? 'Pesanan COD Anda sedang diproses'
                    : 'Pesanan Anda sedang diproses';

                $this->firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'type' => 'order_processing',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id,
                        'payment_method' => $order->transaction->payment_method
                    ],
                    'Pesanan Diproses',
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
            if ($order->order_status !== Order::STATUS_PROCESSING) {
                return ResponseFormatter::error(
                    null,
                    'Only processing orders can be marked as ready',
                    422
                );
            }

            // Update order status
            $order->order_status = Order::STATUS_READY;
            $order->save();

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

                $this->firebaseService->sendToUser(
                    $courierTokens,
                    [
                        'type' => 'order_ready_pickup',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id,
                        'is_cod' => $isCOD,
                        'total_amount' => $isCOD ? $order->transaction->total_price : 0,
                        'merchant' => [
                            'id' => $merchant->id,
                            'name' => $merchant->name,
                            'address' => $merchant->address
                        ]
                    ],
                    'Pesanan Siap Diambil',
                    $isCOD
                        ? "Pesanan COD #{$order->id} siap diambil di {$merchant->name}. Jumlah yang harus ditagih: Rp {$order->transaction->total_price}"
                        : "Pesanan #{$order->id} siap diambil di {$merchant->name}"
                );
            }

            // Notify customer
            if ($order->transaction && $order->transaction->user) {
                $customerTokens = $order->transaction->user->fcmTokens()
                    ->where('is_active', true)
                    ->pluck('token')
                    ->toArray();

                if (!empty($customerTokens)) {
                    $this->firebaseService->sendToUser(
                        $customerTokens,
                        [
                            'type' => 'order_ready_pickup',
                            'order_id' => $order->id,
                            'transaction_id' => $order->transaction->id
                        ],
                        'Pesanan Siap',
                        'Pesanan Anda sudah siap dan akan segera diambil oleh kurir'
                    );
                }
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction', 'orderItems.product.merchant']),
                'Pesanan siap untuk diambil'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal menandai pesanan siap: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Complete the order (after delivery)
     */
    public function complete($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction'])->findOrFail($id);

            // Validate current status
            if ($order->order_status !== Order::STATUS_PICKED_UP) {
                return ResponseFormatter::error(
                    null,
                    'Only orders in picked up status can be completed',
                    422
                );
            }

            // Update order status
            $order->order_status = Order::STATUS_COMPLETED;
            $order->save();

            // Update transaction status
            $order->transaction->status = 'COMPLETED';

            // For COD orders, mark payment as completed upon delivery
            if ($order->transaction->payment_method === 'MANUAL') {
                $order->transaction->payment_status = 'COMPLETED';
                $order->transaction->payment_date = now();
            }

            $order->transaction->save();

            // Notify customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'MANUAL'
                    ? 'Pesanan COD Anda telah diantar dan pembayaran diterima'
                    : 'Pesanan Anda telah diantar';

                $this->firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'type' => 'order_completed',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id,
                        'payment_method' => $order->transaction->payment_method
                    ],
                    'Pesanan Selesai',
                    $message
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction']),
                'Pesanan selesai'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal menyelesaikan pesanan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel the order
     */
    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['transaction'])->findOrFail($id);

            // Validate if order can be cancelled
            if (!in_array($order->order_status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
                return ResponseFormatter::error(
                    null,
                    'Hanya pesanan dengan status pending atau processing yang dapat dibatalkan',
                    422
                );
            }

            // Update order status
            $order->order_status = Order::STATUS_CANCELED;
            $order->save();

            // Update transaction status
            $order->transaction->status = 'CANCELED';

            // For online payment, handle refund if payment was completed
            if ($order->transaction->payment_method === 'ONLINE' &&
                $order->transaction->payment_status === 'COMPLETED') {
                // Handle refund logic here if needed
            }

            $order->transaction->save();

            // Notify customer
            $firebaseService = new FirebaseService();
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $message = $order->transaction->payment_method === 'ONLINE' &&
                          $order->transaction->payment_status === 'COMPLETED'
                    ? 'Pesanan Anda telah dibatalkan. Refund akan diproses.'
                    : 'Pesanan Anda telah dibatalkan';

                $this->firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'type' => 'order_canceled',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id,
                        'refund_needed' => $order->transaction->payment_method === 'ONLINE' &&
                                         $order->transaction->payment_status === 'COMPLETED'
                    ],
                    'Pesanan Dibatalkan',
                    $message
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order->load(['transaction']),
                'Pesanan berhasil dibatalkan'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal membatalkan pesanan: ' . $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Services\FirebaseService;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send notification to merchant for new transaction
     */
    public function sendNewTransactionNotification($merchantId, $orderId, $transactionId)
    {
        try {
            // Load merchant with user and active FCM tokens
            $merchant = Merchant::with(['user' => function($query) {
                $query->with(['fcmTokens' => function($q) {
                    $q->where('is_active', true);
                }]);
            }])->find($merchantId);

            if (!$merchant || !$merchant->user) {
                Log::warning('Merchant or user not found:', ['merchant_id' => $merchantId]);
                return false;
            }

            $tokens = $merchant->user->fcmTokens->pluck('token')->toArray();
            if (empty($tokens)) {
                Log::warning('No active FCM tokens found for merchant:', [
                    'merchant_id' => $merchantId,
                    'user_id' => $merchant->user->id
                ]);
                return false;
            }

            // Get order items for this merchant
            $order = Order::with(['orderItems' => function($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId)->with('product:id,name');
            }])->find($orderId);

            if (!$order) {
                Log::warning('Order not found:', ['order_id' => $orderId]);
                return false;
            }

            // Format order items
            $items = $order->orderItems->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity
                ];
            })->toArray();

            // Format item names for notification message
            $itemNames = $order->orderItems->map(function ($item) {
                return $item->quantity . 'x ' . $item->product->name;
            })->join(', ');

            // Send notification with minimal data
            return $this->firebaseService->sendToUser(
                $tokens,
                [
                    'order_id' => $orderId,
                    'items' => $items
                ],
                '📦 Pesanan Baru!',
                'Pesanan baru untuk: ' . $itemNames
            );

        } catch (Exception $e) {
            Log::error('Error sending notification to merchant:', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification for transaction cancellation
     */
    public function sendTransactionCanceledNotification($merchantId, $orderId, $transactionId)
    {
        try {
            // Load merchant with user and active FCM tokens
            $merchant = Merchant::with(['user' => function($query) {
                $query->with(['fcmTokens' => function($q) {
                    $q->where('is_active', true);
                }]);
            }])->find($merchantId);

            if (!$merchant || !$merchant->user) {
                Log::warning('Merchant or user not found:', ['merchant_id' => $merchantId]);
                return false;
            }

            $tokens = $merchant->user->fcmTokens->pluck('token')->toArray();
            if (empty($tokens)) {
                Log::warning('No active FCM tokens found for merchant:', [
                    'merchant_id' => $merchantId,
                    'user_id' => $merchant->user->id
                ]);
                return false;
            }

            // Get order items for this merchant
            $order = Order::with(['orderItems' => function($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId)->with('product:id,name');
            }])->find($orderId);

            if (!$order) {
                Log::warning('Order not found:', ['order_id' => $orderId]);
                return false;
            }

            // Format order items
            $items = $order->orderItems->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity
                ];
            })->toArray();

            // Format item names for notification message
            $itemNames = $order->orderItems->map(function ($item) {
                return $item->quantity . 'x ' . $item->product->name;
            })->join(', ');

            // Send notification
            return $this->firebaseService->sendToUser(
                $tokens,
                [
                    'order_id' => $orderId,
                    'items' => $items
                ],
                '❌ Pesanan Dibatalkan',
                'Pesanan dibatalkan: ' . $itemNames
            );

        } catch (Exception $e) {
            Log::error('Error sending cancellation notification to merchant:', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification for order status update
     */
    public function sendOrderStatusUpdateNotification($userId, $orderId, $status)
    {
        try {
            // Load user with active FCM tokens
            $user = User::with(['fcmTokens' => function($query) {
                $query->where('is_active', true);
            }])->find($userId);

            if (!$user) {
                Log::warning('User not found:', ['user_id' => $userId]);
                return false;
            }

            $tokens = $user->fcmTokens->pluck('token')->toArray();
            if (empty($tokens)) {
                Log::warning('No active FCM tokens found for user:', ['user_id' => $userId]);
                return false;
            }

            $statusMessage = match ($status) {
                'ACCEPTED' => 'Pesanan Anda telah diterima oleh merchant',
                'REJECTED' => 'Pesanan Anda telah ditolak oleh merchant',
                'PROCESSING' => 'Pesanan Anda sedang diproses',
                'SHIPPED' => 'Pesanan Anda sedang dalam pengiriman',
                'DELIVERED' => 'Pesanan Anda telah sampai',
                'COMPLETED' => 'Pesanan Anda telah selesai',
                'CANCELED' => 'Pesanan Anda telah dibatalkan',
                default => 'Status pesanan Anda telah diperbarui'
            };

            // Get order items
            $order = Order::with(['orderItems.product:id,name'])->find($orderId);

            // Format order items
            $items = $order->orderItems->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity
                ];
            })->toArray();

            // Format item names for notification message
            $itemNames = $order->orderItems->map(function ($item) {
                return $item->quantity . 'x ' . $item->product->name;
            })->join(', ');

            // Send notification
            return $this->firebaseService->sendToUser(
                $tokens,
                [
                    'order_id' => $orderId,
                    'status' => $status,
                    'items' => $items
                ],
                '🔄 Status Pesanan Diperbarui',
                $statusMessage . ': ' . $itemNames
            );

        } catch (Exception $e) {
            Log::error('Error sending order status update notification:', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Test sending notification to merchant
     */
    public function testMerchantNotification(Request $request)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id'
            ]);

            // Load merchant with user and active FCM tokens
            $merchant = Merchant::with(['user' => function($query) {
                $query->with(['fcmTokens' => function($q) {
                    $q->where('is_active', true);
                }]);
            }])->find($request->merchant_id);

            if (!$merchant || !$merchant->user) {
                return ResponseFormatter::error(
                    null,
                    'Merchant atau pengguna tidak ditemukan',
                    404
                );
            }

            $tokens = $merchant->user->fcmTokens->pluck('token')->toArray();
            if (empty($tokens)) {
                return ResponseFormatter::error(
                    null,
                    'Tidak ditemukan token FCM yang aktif untuk merchant',
                    404
                );
            }

            Log::info('Sending test notification to merchant:', [
                'merchant_id' => $request->merchant_id,
                'tokens' => $tokens
            ]);

            // Send test notification
            $result = $this->firebaseService->sendToUser(
                $tokens,
                [
                    'order_id' => 'test_123',
                    'items' => [
                        [
                            'name' => 'Test Product',
                            'quantity' => 1
                        ]
                    ]
                ],
                '🔔 Notifikasi Test',
                'Ini adalah notifikasi test dari Antarkanma'
            );

            if ($result) {
                return ResponseFormatter::success(
                    [
                        'merchant_id' => $request->merchant_id,
                        'tokens' => $tokens
                    ],
                    'Notifikasi test berhasil dikirim'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Gagal mengirim notifikasi test',
                    500
                );
            }

        } catch (Exception $e) {
            Log::error('Error sending test notification:', [
                'merchant_id' => $request->merchant_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseFormatter::error(
                null,
                'Gagal mengirim notifikasi test: ' . $e->getMessage(),
                500
            );
        }
    }
}

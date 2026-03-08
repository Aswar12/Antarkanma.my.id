<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Transaction;
use App\Models\Courier;
use App\Models\Order;
use App\Services\FirebaseService;
use App\Services\OsrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourierController extends Controller
{
    protected $firebaseService;
    protected $osrmService;

    public function __construct(FirebaseService $firebaseService, OsrmService $osrmService)
    {
        $this->firebaseService = $firebaseService;
        $this->osrmService = $osrmService;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER: Pastikan request dari COURIER yang valid
    // ──────────────────────────────────────────────────────────────────────────
    private function getCourier(Request $request)
    {
        if ($request->user()->roles !== 'COURIER') {
            return null;
        }
        return Courier::where('user_id', $request->user()->id)->first();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. GET: Transaksi baru yang tersedia (READY_FOR_PICKUP, belum ada kurir)
    // ──────────────────────────────────────────────────────────────────────────
    public function getNewTransactions(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $perPage = $request->input('per_page', 10);
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            if (!$latitude || !$longitude) {
                return ResponseFormatter::error(null, 'Latitude and longitude are required', 422);
            }

            // HYBRID SYSTEM: Kurir hanya lihat order yang READY (sudah siap diambil)
            $transactions = Transaction::with([
                'baseMerchant',
                'orders.orderItems.product.merchant',
                'orders.orderItems.product.galleries',
                'user',
                'userLocation'
            ])
                ->whereHas('orders', function ($q) {
                    $q->where('order_status', Order::STATUS_READY);
                })
                ->whereNull('courier_id')
                ->where('status', '!=', Transaction::STATUS_CANCELED)
                ->get();

            Log::info("Found {$transactions->count()} READY transactions for courier at {$latitude}, {$longitude}");

            $transactionsWithDistance = $transactions->map(function ($transaction) use ($latitude, $longitude) {
                $route = $this->osrmService->getRouteDistance(
                    $latitude,
                    $longitude,
                    $transaction->baseMerchant->latitude,
                    $transaction->baseMerchant->longitude
                );

                $transaction = $transaction->toArray();
                if ($route) {
                    $transaction['distance'] = $route['distance'];
                    $transaction['duration'] = $route['duration'];
                    $transaction['is_fallback'] = $route['is_fallback'] ?? false;
                }

                if (isset($transaction['user_location'])) {
                    $userLocation = $transaction['user_location'];
                    $transaction['delivery_location'] = [
                        'address'   => $userLocation['address'],
                        'latitude'  => $userLocation['latitude'],
                        'longitude' => $userLocation['longitude'],
                        'notes'     => $userLocation['notes'] ?? null
                    ];
                }

                unset($transaction['courier']);
                return $transaction;
            })->sortBy('distance')->values();

            $page = $request->input('page', 1);
            $paginatedTransactions = new \Illuminate\Pagination\LengthAwarePaginator(
                $transactionsWithDistance->forPage($page, $perPage),
                $transactionsWithDistance->count(),
                $perPage,
                $page
            );

            return ResponseFormatter::success($paginatedTransactions, 'New transactions retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve new transactions: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. GET: Transaksi milik kurir ini
    // ──────────────────────────────────────────────────────────────────────────
    public function getCourierTransactions(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $courier = $request->user()->courier;
            $query = $courier->transactions()->with([
                'orders.orderItems.product.merchant',
                'orders.orderItems.product.galleries',
                'user',
                'userLocation',
                'baseMerchant'
            ]);

            if ($request->has('order_status')) {
                $orderStatus = $request->order_status;
                if (in_array($orderStatus, [
                    Order::STATUS_PENDING, Order::STATUS_WAITING_APPROVAL, Order::STATUS_PROCESSING,
                    Order::STATUS_READY, Order::STATUS_PICKED_UP, Order::STATUS_COMPLETED, Order::STATUS_CANCELED
                ])) {
                    $query->whereHas('orders', function ($q) use ($orderStatus) {
                        $q->where('order_status', $orderStatus);
                    });
                }
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            $sortDirection = in_array($request->input('sort_direction', 'desc'), ['asc', 'desc'])
                ? $request->input('sort_direction', 'desc') : 'desc';
            $query->orderBy('created_at', $sortDirection);

            $transactions = $query->paginate($request->input('per_page', 10));

            return ResponseFormatter::success($transactions, 'Data transaksi berhasil diambil');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Gagal mengambil data transaksi: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. POST: Kurir menerima transaksi
    // FIX: Tidak lagi reset order_status ke PROCESSING.
    //      Order tetap READY_FOR_PICKUP. Hanya courier_id & courier_status yang diset.
    // ──────────────────────────────────────────────────────────────────────────
    public function approveTransaction(Request $request, $id)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $transaction = Transaction::with([
                'orders.orderItems.product.merchant',
                'courier.user',
                'user',
                'userLocation',
                'baseMerchant'
            ])->findOrFail($id);

            if ($transaction->courier_approval !== Transaction::COURIER_PENDING) {
                return ResponseFormatter::error(null, 'Transaksi tidak dapat disetujui: Status tidak valid', 422);
            }

            if (!is_null($transaction->courier_id)) {
                return ResponseFormatter::error(null, 'Transaksi sudah diambil kurir lain', 422);
            }

            $courier = Courier::where('user_id', $request->user()->id)->first();
            if (!$courier) {
                return ResponseFormatter::error(null, 'Unauthorized: User is not registered as a courier', 403);
            }

            DB::beginTransaction();

            // Set courier_id dan approval status di Transaction
            $transaction->courier_id = $courier->id;
            $transaction->courier_approval = Transaction::COURIER_APPROVED;
            // ✅ FIX: Set courier_status ke HEADING_TO_MERCHANT (bukan mengubah order_status!)
            $transaction->courier_status = Transaction::COURIER_STATUS_HEADING_TO_MERCHANT;
            $transaction->save();

            // ✅ FIX: Order status TIDAK BERUBAH — tetap READY_FOR_PICKUP
            // (Kode lama yang salah: foreach orders → status = PROCESSING sudah dihapus)

            // Kirim notifikasi
            try {
                // Notifikasi ke setiap merchant: kurir sedang menuju
                foreach ($transaction->orders as $order) {
                    $merchantTokens = $order->merchant->owner->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();

                    if (!empty($merchantTokens)) {
                        $this->firebaseService->sendToUser(
                            $merchantTokens,
                            [
                                'type'           => 'courier_heading_to_merchant',
                                'transaction_id' => $transaction->id,
                                'order_id'       => $order->id,
                            ],
                            'Kurir Sedang Menuju',
                            'Kurir sedang menuju ke toko Anda untuk mengambil pesanan #' . $order->id
                        );
                    }
                }

                // Notifikasi ke kurir sendiri: konfirmasi diterima
                $courierUser = $request->user();
                $courierTokens = $courierUser->fcmTokens()
                    ->where('is_active', true)->pluck('token')->toArray();
                if (!empty($courierTokens)) {
                    $this->firebaseService->sendToUser(
                        $courierTokens,
                        ['type' => 'order_assigned', 'transaction_id' => $transaction->id],
                        'Pesanan Diterima',
                        'Anda telah menerima pesanan #' . $transaction->id . '. Segera menuju merchant.'
                    );
                }

                // Notifikasi ke customer: kurir ditemukan
                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($customerTokens)) {
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            ['type' => 'courier_found', 'transaction_id' => $transaction->id],
                            'Kurir Ditemukan',
                            'Kurir telah menerima pesanan Anda dan sedang menuju merchant.'
                        );

                        // Save to Database Inbox
                        \App\Http\Controllers\API\NotificationController::createInboxNotification(
                            $transaction->user,
                            'courier_found',
                            'Kurir Ditemukan',
                            'Kurir telah menerima pesanan Anda dan sedang menuju merchant.',
                            ['transaction_id' => $transaction->id]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notifications on approveTransaction: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(null, 'Transaksi berhasil diterima. Silakan menuju merchant.');

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal menyetujui transaksi: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. POST: Kurir lapor sudah sampai di merchant
    // ──────────────────────────────────────────────────────────────────────────
    public function arriveAtMerchant(Request $request, $id)
    {
        try {
            $courier = $this->getCourier($request);
            if (!$courier) {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $transaction = Transaction::with([
                'orders.orderItems.product.merchant',
                'user'
            ])->findOrFail($id);

            // Validasi: transaksi milik kurir ini
            if ($transaction->courier_id !== $courier->id) {
                return ResponseFormatter::error(null, 'Unauthorized: Transaksi tidak milik kurir ini', 403);
            }

            // Validasi: kurir memang sedang heading to merchant
            if ($transaction->courier_status !== Transaction::COURIER_STATUS_HEADING_TO_MERCHANT) {
                return ResponseFormatter::error(
                    null,
                    'Tidak valid: Status kurir saat ini adalah ' . $transaction->courier_status,
                    422
                );
            }

            DB::beginTransaction();

            $transaction->courier_status = Transaction::COURIER_STATUS_AT_MERCHANT;
            $transaction->save();

            // Notifikasi ke merchant: kurir sudah tiba
            try {
                foreach ($transaction->orders as $order) {
                    // Hanya notif untuk order yang masih aktif (bukan CANCELED)
                    if ($order->order_status === Order::STATUS_CANCELED) continue;

                    $merchantTokens = $order->merchant->owner->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();

                    if (!empty($merchantTokens)) {
                        $this->firebaseService->sendToUser(
                            $merchantTokens,
                            [
                                'type'           => 'courier_arrived_at_merchant',
                                'transaction_id' => $transaction->id,
                                'order_id'       => $order->id,
                            ],
                            'Kurir Sudah Tiba! 🛵',
                            'Kurir sudah tiba di toko Anda. Siapkan pesanan #' . $order->id . ' untuk diserahkan.'
                        );
                    }
                }

                // Notifikasi ke customer
                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($customerTokens)) {
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            ['type' => 'courier_at_merchant', 'transaction_id' => $transaction->id],
                            'Kurir di Merchant',
                            'Kurir sedang mengambil pesanan Anda di merchant.'
                        );

                        // Save to Database Inbox
                        \App\Http\Controllers\API\NotificationController::createInboxNotification(
                            $transaction->user,
                            'courier_at_merchant',
                            'Kurir di Merchant',
                            'Kurir sedang mengambil pesanan Anda di merchant.',
                            ['transaction_id' => $transaction->id]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send arriveAtMerchant notifications: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(
                ['courier_status' => Transaction::COURIER_STATUS_AT_MERCHANT],
                'Status berhasil diupdate: Kurir sudah di merchant.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal update status: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. POST: Kurir pickup per-order (bisa partial untuk multi-merchant)
    // ──────────────────────────────────────────────────────────────────────────
    public function pickupOrder(Request $request, $orderId)
    {
        try {
            $courier = $this->getCourier($request);
            if (!$courier) {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $order = Order::with(['transaction', 'orderItems.product.merchant'])->findOrFail($orderId);

            // Validasi: order ini milik transaksi yang di-handle kurir ini
            if ($order->transaction->courier_id !== $courier->id) {
                return ResponseFormatter::error(null, 'Unauthorized: Order tidak milik transaksi kurir ini', 403);
            }

            // Validasi: order harus READY_FOR_PICKUP
            if ($order->order_status !== Order::STATUS_READY) {
                return ResponseFormatter::error(
                    null,
                    'Order tidak bisa di-pickup: status saat ini adalah ' . $order->order_status,
                    422
                );
            }

            DB::beginTransaction();

            $order->order_status = Order::STATUS_PICKED_UP;
            $order->save();

            $transaction = $order->transaction;

            // Cek apakah SEMUA order non-canceled sudah PICKED_UP
            $allPickedUp = !$transaction->orders()
                ->whereNotIn('order_status', [Order::STATUS_PICKED_UP, Order::STATUS_CANCELED, Order::STATUS_COMPLETED])
                ->exists();

            if ($allPickedUp) {
                // Semua order sudah diambil → kurir menuju customer
                $transaction->courier_status = Transaction::COURIER_STATUS_HEADING_TO_CUSTOMER;
                $transaction->save();
            }

            // Notifikasi ke merchant dan customer
            try {
                $merchantTokens = $order->merchant->owner->fcmTokens()
                    ->where('is_active', true)->pluck('token')->toArray();
                if (!empty($merchantTokens)) {
                    $this->firebaseService->sendToUser(
                        $merchantTokens,
                        ['type' => 'order_picked_up', 'order_id' => $order->id, 'transaction_id' => $transaction->id],
                        'Pesanan Diambil ✅',
                        'Pesanan #' . $order->id . ' telah diambil oleh kurir.'
                    );
                }

                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($customerTokens)) {
                        $message = $allPickedUp
                            ? 'Kurir sedang dalam perjalanan menuju Anda! 🚀'
                            : 'Sebagian pesanan sudah diambil. Kurir masih menuju merchant lain.';
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            ['type' => 'order_picked_up', 'order_id' => $order->id, 'transaction_id' => $transaction->id],
                            'Pesanan Diambil',
                            $message
                        );

                        // Save to Database Inbox
                        \App\Http\Controllers\API\NotificationController::createInboxNotification(
                            $transaction->user,
                            'order_picked_up',
                            'Pesanan Diambil',
                            $message,
                            ['order_id' => $order->id, 'transaction_id' => $transaction->id]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send pickupOrder notifications: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(
                [
                    'order_status'   => Order::STATUS_PICKED_UP,
                    'courier_status' => $transaction->fresh()->courier_status,
                    'all_picked_up'  => $allPickedUp,
                ],
                'Pesanan berhasil diambil.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal pickup order: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. POST: Kurir lapor sudah sampai di lokasi customer
    // ──────────────────────────────────────────────────────────────────────────
    public function arriveAtCustomer(Request $request, $id)
    {
        try {
            $courier = $this->getCourier($request);
            if (!$courier) {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $transaction = Transaction::with(['user'])->findOrFail($id);

            if ($transaction->courier_id !== $courier->id) {
                return ResponseFormatter::error(null, 'Unauthorized: Transaksi tidak milik kurir ini', 403);
            }

            if ($transaction->courier_status !== Transaction::COURIER_STATUS_HEADING_TO_CUSTOMER) {
                return ResponseFormatter::error(
                    null,
                    'Tidak valid: Semua order harus sudah diambil sebelum lapor ke customer.',
                    422
                );
            }

            DB::beginTransaction();

            $transaction->courier_status = Transaction::COURIER_STATUS_AT_CUSTOMER;
            $transaction->save();

            // Notifikasi ke customer
            try {
                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($customerTokens)) {
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            ['type' => 'courier_arrived_at_customer', 'transaction_id' => $transaction->id],
                            'Kurir Sudah Tiba! 🎉',
                            'Kurir sudah tiba di lokasi Anda. Segera ambil pesanan Anda.'
                        );

                        // Save to Database Inbox
                        \App\Http\Controllers\API\NotificationController::createInboxNotification(
                            $transaction->user,
                            'courier_arrived_at_customer',
                            'Kurir Sudah Tiba! 🎉',
                            'Kurir sudah tiba di lokasi Anda. Segera ambil pesanan Anda.',
                            ['transaction_id' => $transaction->id]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send arriveAtCustomer notifications: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(
                ['courier_status' => Transaction::COURIER_STATUS_AT_CUSTOMER],
                'Status berhasil diupdate: Kurir sudah di lokasi customer.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal update status: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 7. POST: Kurir selesaikan order (per-order) + auto-complete Transaction
    // ──────────────────────────────────────────────────────────────────────────
    public function completeOrder(Request $request, $orderId)
    {
        try {
            $courier = $this->getCourier($request);
            if (!$courier) {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $order = Order::with(['transaction.user', 'transaction.orders'])->findOrFail($orderId);

            // Validasi: order ini milik transaksi kurir ini
            if ($order->transaction->courier_id !== $courier->id) {
                return ResponseFormatter::error(null, 'Unauthorized: Order tidak milik transaksi kurir ini', 403);
            }

            // Validasi: order harus PICKED_UP
            if ($order->order_status !== Order::STATUS_PICKED_UP) {
                return ResponseFormatter::error(
                    null,
                    'Order tidak bisa diselesaikan: status saat ini adalah ' . $order->order_status,
                    422
                );
            }

            DB::beginTransaction();

            $order->order_status = Order::STATUS_COMPLETED;
            $order->save();

            $transaction = $order->transaction;

            // Cek apakah SEMUA order sudah COMPLETED atau CANCELED
            $allDone = $transaction->allOrdersCompleted();
            $transactionCompleted = false;

            if ($allDone) {
                $transaction->status = Transaction::STATUS_COMPLETED;
                $transaction->courier_status = Transaction::COURIER_STATUS_DELIVERED;
                $transaction->save();
                $transactionCompleted = true;

                Log::info("Transaction #{$transaction->id} auto-completed: all orders finished.");
            }

            // Notifikasi
            try {
                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($customerTokens)) {
                        $title  = $transactionCompleted ? 'Semua Pesanan Selesai! 🎉' : 'Pesanan Selesai ✅';
                        $body   = $transactionCompleted
                            ? 'Semua pesanan Anda sudah tiba. Terima kasih telah menggunakan Antarkanma!'
                            : 'Sebagian pesanan Anda sudah tiba.';
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            ['type' => 'order_completed', 'order_id' => $order->id, 'transaction_id' => $transaction->id, 'transaction_completed' => $transactionCompleted],
                            $title,
                            $body
                        );

                        // Save to Database Inbox
                        \App\Http\Controllers\API\NotificationController::createInboxNotification(
                            $transaction->user,
                            'order_completed',
                            $title,
                            $body,
                            ['order_id' => $order->id, 'transaction_id' => $transaction->id, 'transaction_completed' => $transactionCompleted]
                        );
                    }
                }

                // Notifikasi merchant order selesai
                if ($order->merchant && $order->merchant->owner) {
                    $merchantTokens = $order->merchant->owner->fcmTokens()
                        ->where('is_active', true)->pluck('token')->toArray();
                    if (!empty($merchantTokens)) {
                        $this->firebaseService->sendToUser(
                            $merchantTokens,
                            ['type' => 'order_completed', 'order_id' => $order->id, 'transaction_id' => $transaction->id],
                            'Pesanan Selesai ✅',
                            'Pesanan #' . $order->id . ' telah berhasil diantarkan ke customer.'
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send completeOrder notifications: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(
                [
                    'order_status'          => Order::STATUS_COMPLETED,
                    'transaction_completed' => $transactionCompleted,
                    'courier_status'        => $transaction->fresh()->courier_status,
                ],
                $transactionCompleted
                    ? 'Semua pesanan selesai! Transaksi telah diselesaikan.'
                    : 'Pesanan berhasil diselesaikan.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal menyelesaikan order: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 8. GET: Statistik harian kurir
    // ──────────────────────────────────────────────────────────────────────────
    public function getDailyStatistics(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $courier = $request->user()->courier;
            $today = now()->format('Y-m-d');

            $statistics = [
                'total_orders' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->whereDate('created_at', $today)->count(),

                'completed_orders' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->where('order_status', Order::STATUS_COMPLETED)
                  ->whereDate('created_at', $today)->count(),

                'total_earnings' => Transaction::where('courier_id', $courier->id)
                    ->whereDate('created_at', $today)->sum('shipping_price'),

                'average_delivery_time' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->where('order_status', Order::STATUS_COMPLETED)
                  ->whereDate('created_at', $today)
                  ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, updated_at)'))
            ];

            return ResponseFormatter::success($statistics, 'Daily statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve daily statistics: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 9. GET: Jumlah order per status
    // ──────────────────────────────────────────────────────────────────────────
    public function getStatusCounts(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $courier = $request->user()->courier;
            $statuses = [
                Order::STATUS_PENDING, Order::STATUS_WAITING_APPROVAL, Order::STATUS_PROCESSING,
                Order::STATUS_READY, Order::STATUS_PICKED_UP, Order::STATUS_COMPLETED, Order::STATUS_CANCELED
            ];

            $counts = Transaction::where('courier_id', $courier->id)
                ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
                ->select('orders.order_status', DB::raw('count(*) as total'))
                ->groupBy('orders.order_status')
                ->pluck('total', 'order_status')
                ->toArray();

            $result = array_fill_keys($statuses, 0);
            foreach ($counts as $status => $count) {
                if (isset($result[$status])) {
                    $result[$status] = $count;
                }
            }

            return ResponseFormatter::success($result, 'Status counts retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve status counts: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 10. PUT: Update courier status (online/offline)
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $courier = Courier::where('user_id', $request->user()->id)->first();

            if (!$courier || $courier->id != $id) {
                return ResponseFormatter::error(null, 'Unauthorized: Courier not found', 404);
            }

            $validated = $request->validate([
                'is_active' => 'boolean',
                'vehicle_type' => 'string|max:255',
                'license_plate' => 'string|max:255',
                'wallet_balance' => 'numeric|min:0',
                'is_wallet_active' => 'boolean',
            ]);

            $courier->update($validated);

            return ResponseFormatter::success($courier, 'Courier updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(null, 'Validation failed: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update courier: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 11. GET: Get courier profile
    // ──────────────────────────────────────────────────────────────────────────
    public function getProfile(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $courier = Courier::where('user_id', $request->user()->id)
                ->with('user')
                ->first();

            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            return ResponseFormatter::success($courier, 'Courier profile retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve courier profile: ' . $e->getMessage(), 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 12. GET: Wallet balance
    // ──────────────────────────────────────────────────────────────────────────
    public function getWalletBalance(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            $courier = Courier::where('user_id', $request->user()->id)->first();
            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            return ResponseFormatter::success([
                'balance' => $courier->wallet_balance,
                'is_wallet_active' => $courier->is_wallet_active,
                'fee_per_order' => $courier->fee_per_order,
                'minimum_balance' => $courier->minimum_balance,
            ], 'Wallet balance retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Get wallet balance error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to get wallet balance', 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 13. POST: Withdraw courier earnings
    // ──────────────────────────────────────────────────────────────────────────
    public function withdraw(Request $request)
    {
        try {
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(null, 'Unauthorized: User is not a courier', 403);
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:10000', // Minimum withdrawal Rp 10.000
            ]);

            $courier = Courier::where('user_id', $request->user()->id)->first();

            if (!$courier) {
                return ResponseFormatter::error(null, 'Courier profile not found', 404);
            }

            if (!$courier->is_wallet_active) {
                return ResponseFormatter::error(null, 'Wallet tidak aktif. Hubungi admin untuk aktivasi.', 422);
            }

            $amount = $validated['amount'];

            if ($courier->wallet_balance < $amount) {
                return ResponseFormatter::error(null, 'Saldo tidak mencukupi untuk penarikan ini.', 422);
            }

            // Check minimum balance requirement
            $minimumBalance = $courier->minimum_balance ?? 0;
            if (($courier->wallet_balance - $amount) < $minimumBalance) {
                return ResponseFormatter::error(
                    null, 
                    'Penarikan gagal. Pastikan sisa saldo minimal Rp ' . number_format($minimumBalance, 0, ',', '.'), 
                    422
                );
            }

            DB::beginTransaction();

            // Deduct from wallet balance
            $courier->wallet_balance -= $amount;
            $courier->save();

            // Create withdrawal record (you can create a withdrawals table if needed)
            // For now, we'll just log it
            \App\Models\Transaction::create([
                'courier_id' => $courier->id,
                'status' => 'withdrawal',
                'total_price' => -$amount, // Negative for withdrawal
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
            ]);

            DB::commit();

            return ResponseFormatter::success([
                'new_balance' => $courier->wallet_balance,
                'withdrawal_amount' => $amount,
            ], 'Penarikan berhasil diproses. Dana akan ditransfer dalam 1-3 hari kerja.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(null, 'Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal memproses penarikan: ' . $e->getMessage(), 500);
        }
    }
}

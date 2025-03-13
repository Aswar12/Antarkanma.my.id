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

    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            // Find the specific order
            $order = Order::with(['transaction', 'orderItems.product.merchant'])->findOrFail($orderId);

            // Verify this order belongs to the courier's transaction
            if ($order->transaction->courier_id !== $request->user()->courier->id) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: Order does not belong to this courier',
                    403
                );
            }

            // Validate order status
            if ($order->order_status !== Order::STATUS_READY) {
                return ResponseFormatter::error(
                    null,
                    'Only orders that are ready can be picked up',
                    422
                );
            }

            DB::beginTransaction();

            // Update order status to PICKED_UP
            $order->order_status = Order::STATUS_PICKED_UP;
            $order->save();

            // Send notification to merchant
            $merchantTokens = $order->merchant->owner->fcmTokens()
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (!empty($merchantTokens)) {
                $this->firebaseService->sendToUser(
                    $merchantTokens,
                    [
                        'type' => 'order_picked_up',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id
                    ],
                    'Pesanan Diambil',
                    'Pesanan #' . $order->id . ' telah diambil oleh kurir'
                );
            }

            // Notify customer
            $customerTokens = $order->user->fcmTokens->pluck('token')->toArray();
            if (!empty($customerTokens)) {
                $this->firebaseService->sendToUser(
                    $customerTokens,
                    [
                        'type' => 'order_in_transit',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction->id
                    ],
                    'Pesanan Dalam Perjalanan',
                    'Pesanan Anda sedang dalam perjalanan'
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $order,
                'Order picked up successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Failed to update order status: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getStatusCounts(Request $request)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            $courier = $request->user()->courier;

            // Get all possible order statuses
            $statuses = [
                Order::STATUS_PENDING,
                Order::STATUS_WAITING_APPROVAL,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
                Order::STATUS_PICKED_UP,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELED
            ];

            // Get counts for each status
            $counts = Transaction::where('courier_id', $courier->id)
                ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
                ->select('orders.order_status', DB::raw('count(*) as total'))
                ->groupBy('orders.order_status')
                ->pluck('total', 'order_status')
                ->toArray();

            // Initialize result array with all statuses set to 0
            $result = array_fill_keys($statuses, 0);

            // Update counts for statuses that have orders
            foreach ($counts as $status => $count) {
                if (isset($result[$status])) {
                    $result[$status] = $count;
                }
            }

            return ResponseFormatter::success(
                $result,
                'Status counts retrieved successfully'
            );

        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to retrieve status counts: ' . $e->getMessage(),
                500
            );
        }
    }



    public function getNewTransactions(Request $request)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            $perPage = $request->input('per_page', 10);
            $userId = $request->user()->id;

            // First, cancel any timed out transactions
            $timedOutTransactions = Transaction::where('status', Transaction::STATUS_PENDING)
                ->where('courier_approval', Transaction::COURIER_PENDING)
                ->where('timeout_at', '<', now())
                ->get();

            foreach ($timedOutTransactions as $transaction) {
                $transaction->status = Transaction::STATUS_CANCELED;
                $transaction->save();

                // Cancel all associated orders
                $transaction->orders()->update([
                    'order_status' => Order::STATUS_CANCELED
                ]);

                Log::info("Transaction {$transaction->id} automatically canceled due to timeout");
            }

            // Get courier's current location
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            if (!$latitude || !$longitude) {
                return ResponseFormatter::error(
                    null,
                    'Latitude and longitude are required',
                    422
                );
            }

            // Get valid transactions with base merchant and user location
            $transactions = Transaction::with([
                'baseMerchant',
                'orders.orderItems.product.merchant',
                'orders.orderItems.product.galleries',
                'user',
                'userLocation'
            ])
                ->where('courier_approval', Transaction::COURIER_PENDING)
                ->whereNull('courier_id')
                ->where('status', Transaction::STATUS_PENDING)
                ->where('timeout_at', '>', now())
                ->get();

            // Calculate distance using OSRM and add it to each transaction
            $transactionsWithDistance = $transactions->map(function ($transaction) use ($latitude, $longitude) {
                $route = $this->osrmService->getRouteDistance(
                    $latitude,
                    $longitude,
                    $transaction->baseMerchant->latitude,
                    $transaction->baseMerchant->longitude
                );

                $transaction = $transaction->toArray();
                if ($route) {
                    // OSRM service already returns distance in kilometers
                    $transaction['distance'] = $route['distance'];
                    // Duration is already in minutes from OSRM service
                    $transaction['duration'] = $route['duration'];
                    $transaction['is_fallback'] = $route['is_fallback'] ?? false;
                }

                // Add user location details if available
                if (isset($transaction['user_location'])) {
                    $userLocation = $transaction['user_location'];
                    $transaction['delivery_location'] = [
                        'address' => $userLocation['address'],
                        'latitude' => $userLocation['latitude'],
                        'longitude' => $userLocation['longitude'],
                        'notes' => $userLocation['notes'] ?? null
                    ];
                }

                unset($transaction['courier']); // Remove courier data since it's not approved yet

                return $transaction;
            })->sortBy('distance')->values();

            // Paginate the sorted results
            $page = $request->input('page', 1);
            $paginatedTransactions = new \Illuminate\Pagination\LengthAwarePaginator(
                $transactionsWithDistance->forPage($page, $perPage),
                $transactionsWithDistance->count(),
                $perPage,
                $page
            );

            return ResponseFormatter::success(
                $paginatedTransactions,
                'New transactions retrieved successfully'
            );

        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to retrieve new transactions: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getCourierTransactions(Request $request)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            $courier = $request->user()->courier;
            $query = $courier->transactions()
                ->with([
                    'orders.orderItems.product.merchant',
                    'orders.orderItems.product.galleries',
                    'user',
                    'userLocation',
                    'baseMerchant'
                ]);

            // Filter by order status
            if ($request->has('order_status')) {
                $orderStatus = $request->order_status;
                if (in_array($orderStatus, [
                    Order::STATUS_PENDING,
                    Order::STATUS_WAITING_APPROVAL,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_COMPLETED,
                    Order::STATUS_CANCELED
                ])) {
                    $query->whereHas('orders', function ($q) use ($orderStatus) {
                        $q->where('order_status', $orderStatus);
                    });
                }
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Sort by created_at
            $sortDirection = $request->input('sort_direction', 'desc');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }
            $query->orderBy('created_at', $sortDirection);

            $transactions = $query->paginate($request->input('per_page', 10));

            return ResponseFormatter::success(
                $transactions,
                'Data transaksi berhasil diambil'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Gagal mengambil data transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function getDailyStatistics(Request $request)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            $courier = $request->user()->courier;
            $today = now()->format('Y-m-d');

            // Get today's statistics
            $statistics = [
                'total_orders' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->whereDate('created_at', $today)->count(),

                'completed_orders' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->where('order_status', Order::STATUS_COMPLETED)
                  ->whereDate('created_at', $today)->count(),

                'total_earnings' => Transaction::where('courier_id', $courier->id)
                    ->whereDate('created_at', $today)
                    ->sum('delivery_fee'),

                'average_delivery_time' => Order::whereHas('transaction', function ($query) use ($courier) {
                    $query->where('courier_id', $courier->id);
                })->where('order_status', Order::STATUS_COMPLETED)
                  ->whereDate('created_at', $today)
                  ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, updated_at)'))
            ];

            return ResponseFormatter::success(
                $statistics,
                'Daily statistics retrieved successfully'
            );

        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to retrieve daily statistics: ' . $e->getMessage(),
                500
            );
        }
    }

    public function approveTransaction(Request $request, $id)
    {
        try {
            // Check if user has courier role
            if ($request->user()->roles !== 'COURIER') {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not a courier',
                    403
                );
            }

            $transaction = Transaction::with([
                'orders.orderItems.product.merchant',
                'courier.user',
                'user',
                'userLocation',
                'baseMerchant'
            ])->findOrFail($id);

            // Check if transaction can be approved
            if ($transaction->courier_approval !== Transaction::COURIER_PENDING) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak dapat disetujui: Status tidak valid',
                    422
                );
            }

            if (!is_null($transaction->courier_id)) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi sudah diambil kurir lain',
                    422
                );
            }

            DB::beginTransaction();

            // Get the courier record for this user
            $courier = Courier::where('user_id', $request->user()->id)->first();

            if (!$courier) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: User is not registered as a courier',
                    403
                );
            }

            // Update transaction with courier_id from couriers table
            $transaction->courier_id = $courier->id;
            $transaction->courier_approval = Transaction::COURIER_APPROVED;
            $transaction->save();

            // Update all orders status to WAITING_APPROVAL
            foreach ($transaction->orders as $order) {
                $order->order_status = Order::STATUS_WAITING_APPROVAL;
                $order->save();
            }

            // Send notifications to all parties
            try {
                // Notify merchants
                foreach ($transaction->orders as $order) {
                    // Get merchant's FCM tokens
                    $merchantTokens = $order->merchant->owner->fcmTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->toArray();

                    if (!empty($merchantTokens)) {
                        $this->firebaseService->sendToUser(
                            $merchantTokens,
                            [
                                'type' => 'transaction_approved',
                                'transaction_id' => $transaction->id,
                                'order_id' => $order->id
                            ],
                            'Transaksi Disetujui',
                            'Transaksi #' . $transaction->id . ' telah disetujui. Anda memiliki order yang perlu diproses.'
                        );
                    }
                }

                // Notify courier
                if ($transaction->courier && $transaction->courier->user) {
                    $courierTokens = $transaction->courier->user->fcmTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->toArray();

                    if (!empty($courierTokens)) {
                        $this->firebaseService->sendToUser(
                            $courierTokens,
                            [
                                'type' => 'order_assigned',
                                'transaction_id' => $transaction->id
                            ],
                            'Pesanan Diterima',
                            'Anda telah menerima pesanan #' . $transaction->id
                        );
                    }
                }

                // Notify customer
                if ($transaction->user) {
                    $customerTokens = $transaction->user->fcmTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->toArray();

                    if (!empty($customerTokens)) {
                        $this->firebaseService->sendToUser(
                            $customerTokens,
                            [
                                'type' => 'courier_found',
                                'transaction_id' => $transaction->id
                            ],
                            'Kurir Ditemukan',
                            'Kurir telah menerima pesanan Anda #' . $transaction->id
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notifications: ' . $e->getMessage());
            }

            DB::commit();

            return ResponseFormatter::success(
                null,
                'Transaksi berhasil disetujui, menunggu persetujuan merchant'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Gagal menyetujui transaksi: ' . $e->getMessage(),
                500
            );
        }
    }
}

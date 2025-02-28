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

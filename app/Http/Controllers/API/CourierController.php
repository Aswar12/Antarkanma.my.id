<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourierController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $courier = $user->courier()->with(['user', 'transactions', 'deliveries'])->first();

        if (!$courier) {
            return ResponseFormatter::error(
                null,
                'Akun kurir tidak ditemukan',
                404
            );
        }

        // Get active transactions count
        $activeTransactionsCount = $courier->transactions()
            ->whereNotIn('status', [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELED])
            ->count();

        // Get completed transactions count
        $completedTransactionsCount = $courier->transactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->count();

        // Get active deliveries
        $activeDeliveries = $courier->deliveries()
            ->with(['transaction.orders.merchant', 'transaction.userLocation'])
            ->whereHas('transaction', function($query) {
                $query->whereNotIn('status', [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELED]);
            })
            ->get();

        $data = [
            'id' => $courier->id,
            'user' => $courier->user,
            'vehicle_type' => $courier->vehicle_type,
            'license_plate' => $courier->license_plate,
            'full_details' => $courier->full_details,
            'statistics' => [
                'active_transactions' => $activeTransactionsCount,
                'completed_transactions' => $completedTransactionsCount,
                'total_transactions' => $activeTransactionsCount + $completedTransactionsCount
            ],
            'active_deliveries' => $activeDeliveries
        ];

        return ResponseFormatter::success(
            $data,
            'Data profil kurir berhasil diambil'
        );
    }

    public function index(Request $request)
    {
        $courier = Auth::user()->courier;
        $limit = $request->input('limit', 10);
        $status = $request->input('status');

        if (!$courier) {
            return ResponseFormatter::error(
                null,
                'Akun kurir tidak ditemukan',
                404
            );
        }

        $transactions = Transaction::with([
                'orders.merchant', 
                'orders.orderItems.product', 
                'userLocation',
                'user'
            ])
            ->where('courier_id', $courier->id);

        if ($status) {
            $transactions->where('status', $status);
        }

        return ResponseFormatter::success(
            $transactions->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }

    public function show($id)
    {
        $courier = Auth::user()->courier;

        if (!$courier) {
            return ResponseFormatter::error(
                null,
                'Akun kurir tidak ditemukan',
                404
            );
        }

        $transaction = Transaction::with([
                'orders.merchant', 
                'orders.orderItems.product',
                'userLocation',
                'user'
            ])
            ->where('courier_id', $courier->id)
            ->find($id);

        if (!$transaction) {
            return ResponseFormatter::error(
                null,
                'Data transaksi tidak ditemukan',
                404
            );
        }

        return ResponseFormatter::success(
            $transaction,
            'Data transaksi berhasil diambil'
        );
    }

    public function approveTransaction($id)
    {
        try {
            DB::beginTransaction();

            $courier = Auth::user()->courier;
            
            if (!$courier) {
                return ResponseFormatter::error(
                    null,
                    'Akun kurir tidak ditemukan',
                    404
                );
            }

            $transaction = Transaction::where('courier_approval', Transaction::COURIER_PENDING)
                ->find($id);

            if (!$transaction) {
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ditemukan',
                    404
                );
            }

            if ($transaction->isTimedOut()) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi sudah timeout',
                    400
                );
            }

            if (!$transaction->needsCourierApproval()) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak dapat diapprove',
                    400
                );
            }

            // Update transaction
            $transaction->courier_id = $courier->id;
            $transaction->courier_approval = Transaction::COURIER_APPROVED;
            $transaction->save();

            // Update all orders to WAITING_APPROVAL
            $transaction->orders()->update([
                'order_status' => Order::STATUS_WAITING_APPROVAL
            ]);

            DB::commit();

            return ResponseFormatter::success(
                $transaction,
                'Transaksi berhasil diapprove'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Terjadi kesalahan saat approve transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function rejectTransaction($id)
    {
        try {
            DB::beginTransaction();

            $courier = Auth::user()->courier;
            
            if (!$courier) {
                return ResponseFormatter::error(
                    null,
                    'Akun kurir tidak ditemukan',
                    404
                );
            }

            $transaction = Transaction::where('courier_approval', Transaction::COURIER_PENDING)
                ->find($id);

            if (!$transaction) {
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ditemukan',
                    404
                );
            }

            if (!$transaction->needsCourierApproval()) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak dapat direject',
                    400
                );
            }

            // Update transaction
            $transaction->courier_approval = Transaction::COURIER_REJECTED;
            $transaction->status = Transaction::STATUS_CANCELED;
            $transaction->save();

            // Cancel all orders
            $transaction->orders()->update([
                'order_status' => Order::STATUS_CANCELED
            ]);

            DB::commit();

            return ResponseFormatter::success(
                $transaction,
                'Transaksi berhasil direject'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Terjadi kesalahan saat reject transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            $courier = Auth::user()->courier;
            
            if (!$courier) {
                return ResponseFormatter::error(
                    null,
                    'Akun kurir tidak ditemukan',
                    404
                );
            }

            $order = Order::whereHas('transaction', function($query) use ($courier) {
                $query->where('courier_id', $courier->id);
            })->find($orderId);

            if (!$order) {
                return ResponseFormatter::error(
                    null,
                    'Order tidak ditemukan',
                    404
                );
            }

            $newStatus = $request->input('status');
            $allowedStatuses = [Order::STATUS_PICKED_UP, Order::STATUS_COMPLETED];

            if (!in_array($newStatus, $allowedStatuses)) {
                return ResponseFormatter::error(
                    null,
                    'Status tidak valid',
                    400
                );
            }

            // Validate status transition
            $validTransitions = [
                Order::STATUS_READY => [Order::STATUS_PICKED_UP],
                Order::STATUS_PICKED_UP => [Order::STATUS_COMPLETED]
            ];

            if (!isset($validTransitions[$order->order_status]) || 
                !in_array($newStatus, $validTransitions[$order->order_status])) {
                return ResponseFormatter::error(
                    null,
                    'Perubahan status tidak valid',
                    400
                );
            }

            $order->order_status = $newStatus;
            $order->save();

            // Update transaction status if all orders are completed
            if ($newStatus === Order::STATUS_COMPLETED) {
                $transaction = $order->transaction;
                if ($transaction->allOrdersCompleted()) {
                    $transaction->status = Transaction::STATUS_COMPLETED;
                    $transaction->save();
                }
            }

            DB::commit();

            return ResponseFormatter::success(
                $order,
                'Status order berhasil diperbarui'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Terjadi kesalahan saat memperbarui status: ' . $e->getMessage(),
                500
            );
        }
    }
}

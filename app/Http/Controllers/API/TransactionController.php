<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_location_id' => 'required|exists:user_locations,id',
            'total_price' => 'required|numeric',
            'shipping_price' => 'required|numeric',
            'payment_method' => 'required|in:MANUAL,ONLINE',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.merchant_id' => 'required|exists:merchants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try {
            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $request->total_price,
                'order_status' => 'PENDING',
            ]);

            // Create Order Items
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'merchant_id' => $item['merchant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Create Transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'user_location_id' => $request->user_location_id,
                'total_price' => $request->total_price,
                'shipping_price' => $request->shipping_price,
                'status' => 'PENDING',
                'payment_method' => $request->payment_method,
                'payment_status' => 'PENDING',
            ]);

            DB::commit();
            return ResponseFormatter::success($transaction->load('order.items.product'), 'Transaction created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to create transaction: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        try {
            $transaction = Transaction::with('order.items.product')->findOrFail($id);
            if ($transaction->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }
            return ResponseFormatter::success($transaction, 'Transaction retrieved successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error(null, 'Transaction not found', 404);
        }
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status');

        $transactionQuery = Transaction::with('order.items.product')->where('user_id', Auth::id());

        if ($status) {
            $transactionQuery->where('status', $status);
        }

        $transactions = $transactionQuery->paginate($limit);

        return ResponseFormatter::success($transactions, 'Transactions list retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:PENDING,COMPLETED,CANCELED',
            'payment_status' => 'sometimes|required|in:PENDING,COMPLETED,FAILED',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'note' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            $transaction->fill($request->only(['status', 'payment_status', 'rating', 'note']));

            if ($request->has('status') && $request->status === 'COMPLETED') {
                $transaction->payment_date = now();
            }

            $transaction->save();

            // Update related order status
            if ($request->has('status')) {
                $transaction->order->update(['order_status' => $request->status]);
            }

            DB::commit();
            return ResponseFormatter::success($transaction->load('order.items.product'), 'Transaction updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to update transaction: ' . $e->getMessage(), 500);
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized', 403);
            }

            if ($transaction->status !== 'PENDING') {
                return ResponseFormatter::error(null, 'Only pending transactions can be canceled', 400);
            }

            $transaction->status = 'CANCELED';
            $transaction->save();

            // Update related order status
            $transaction->order->update(['order_status' => 'CANCELED']);

            DB::commit();
            return ResponseFormatter::success($transaction, 'Transaction canceled successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to cancel transaction: ' . $e->getMessage(), 500);
        }
    }

    public function getByMerchant(Request $request, $merchantId)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status');

        $transactionQuery = Transaction::with('order.items.product')
            ->whereHas('order.items', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            });

        if ($status) {
            $transactionQuery->where('status', $status);
        }

        $transactions = $transactionQuery->paginate($limit);

        return ResponseFormatter::success($transactions, 'Merchant transactions retrieved successfully');
    }
}

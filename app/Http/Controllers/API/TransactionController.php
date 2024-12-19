<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Product;
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
            'user_location_id' => [
                'required',
                'exists:user_locations,id',
                function ($attribute, $value, $fail) {
                    $userLocation = UserLocation::find($value);
                    if ($userLocation->user_id !== Auth::id()) {
                        $fail('The selected user location does not belong to the authenticated user.');
                    }
                },
            ],
            'total_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $calculatedTotal = collect($request->items)->sum(function ($item) {
                        return $item['price'] * $item['quantity'];
                    });
                    if ($value != $calculatedTotal) {
                        $fail('The total price does not match the sum of item prices.');
                    }
                },
            ],
            'shipping_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:MANUAL,ONLINE',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ], [
            'user_location_id.required' => 'Lokasi pengiriman harus dipilih',
            'user_location_id.exists' => 'Lokasi pengiriman tidak valid',
            'total_price.required' => 'Total harga harus diisi',
            'total_price.numeric' => 'Total harga harus berupa angka',
            'total_price.min' => 'Total harga tidak boleh negatif',
            'shipping_price.required' => 'Biaya pengiriman harus diisi',
            'shipping_price.numeric' => 'Biaya pengiriman harus berupa angka',
            'shipping_price.min' => 'Biaya pengiriman tidak boleh negatif',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'items.required' => 'Daftar item harus diisi',
            'items.array' => 'Format daftar item tidak valid',
            'items.min' => 'Minimal harus ada satu item',
            'items.*.product_id.required' => 'ID produk harus diisi',
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.quantity.required' => 'Jumlah item harus diisi',
            'items.*.quantity.integer' => 'Jumlah item harus berupa angka bulat',
            'items.*.quantity.min' => 'Jumlah item minimal 1',
            'items.*.price.required' => 'Harga item harus diisi',
            'items.*.price.numeric' => 'Harga item harus berupa angka',
            'items.*.price.min' => 'Harga item tidak boleh negatif',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
        }

        DB::beginTransaction();
        try {
            // Hitung total harga dari items
            $totalPrice = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalPrice,
                'order_status' => 'PENDING',
            ]);

            // Create Order Items
            $orderItems = [];
            foreach ($request->items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'merchant_id' => $item['merchant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                $orderItems[] = $orderItem;
            }

            // Create Transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'user_location_id' => $request->user_location_id,
                'total_price' => $totalPrice,
                'shipping_price' => $request->shipping_price,
                'status' => 'PENDING',
                'payment_method' => $request->payment_method,
                'payment_status' => 'PENDING',
            ]);

            // Load relationships
            $transaction->load([
                'order' => function ($query) {
                    $query->with([
                        'orderItems' => function ($q) {
                            $q->with(['product', 'merchant']);
                        }
                    ]);
                }
            ]);

            DB::commit();
            return ResponseFormatter::success($transaction, 'Transaction created successfully');
        } catch (Exception $e) {
            DB::rollBack();

          
            return ResponseFormatter::error(null, 'Failed to create transaction: ' . $e->getMessage(), 500);
        }
    }



    public function get($id)
    {
        try {
            $transaction = Transaction::with('order.orderItems.product')->findOrFail($id);
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

        $transactionQuery = Transaction::with('order.orderItems.product')->where('user_id', Auth::id());

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
            return ResponseFormatter::success($transaction->load('order.orderItems.product'), 'Transaction updated successfully');
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

        $transactionQuery = Transaction::with('order.orderItems.product')
            ->whereHas('order.orderItems', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            });

        if ($status) {
            $transactionQuery->where('status', $status);
        }

        $transactions = $transactionQuery->paginate($limit);

        return ResponseFormatter::success($transactions, 'Merchant transactions retrieved successfully');
    }
}

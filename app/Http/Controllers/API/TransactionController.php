<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserLocation;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function list(Request $request)
    {
        try {
            $transactions = Transaction::with(['user', 'order.orderItems.product'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseFormatter::success(
                $transactions,
                'Data transaksi berhasil diambil'
            );
        } catch (Exception $error) {
            Log::error('Failed to fetch transactions:', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            
            return ResponseFormatter::error(
                null,
                'Data transaksi gagal diambil: ' . $error->getMessage(),
                500
            );
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_location_id' => 'required|exists:user_locations,id',
            'total_price' => 'required|regex:/^\d*(\.\d{2})?$/',
            'shipping_price' => 'required|regex:/^\d*(\.\d{2})?$/',
            'payment_method' => 'required|in:MANUAL,ONLINE',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                'exists:products,id',
                function ($attribute, $value, $fail) use ($request) {
                    $item = collect($request->items)->firstWhere('product_id', $value);
                    $product = Product::find($value);
                    if ($product && $product->merchant_id != $item['merchant']['id']) {
                        $fail('Product does not belong to the specified merchant.');
                    }
                }
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|regex:/^\d*(\.\d{2})?$/',
            'items.*.merchant.id' => [
                'required',
                'exists:merchants,id'
            ],
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
            'items.*.merchant.id.required' => 'ID merchant harus diisi',
            'items.*.merchant.id.exists' => 'Merchant tidak ditemukan'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
        }

        try {
            Log::info('Transaction Create Request:', [
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            DB::beginTransaction();

            try {
                // Prepare order data
                $totalPrice = is_string($request->total_price) ?
                    (float) str_replace(',', '', $request->total_price) :
                    (float) $request->total_price;

                $orderData = [
                    'user_id' => Auth::id(),
                    'total_amount' => $totalPrice,
                    'order_status' => 'PENDING',
                ];

                Log::info('Creating Order:', $orderData);
                $order = Order::create($orderData);

                // Create Order Items
                foreach ($request->items as $item) {
                    $itemPrice = is_string($item['price']) ?
                        (float) str_replace(',', '', $item['price']) :
                        (float) $item['price'];

                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'merchant_id' => $item['merchant']['id'],
                        'quantity' => (int) $item['quantity'],
                        'price' => $itemPrice,
                    ];
                    Log::info('Creating Order Item:', $orderItemData);
                    OrderItem::create($orderItemData);
                }

                // Prepare transaction data
                $shippingPrice = is_string($request->shipping_price) ?
                    (float) str_replace(',', '', $request->shipping_price) :
                    (float) $request->shipping_price;

                $transactionData = [
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'user_location_id' => $request->user_location_id,
                    'total_price' => $totalPrice,
                    'shipping_price' => $shippingPrice,
                    'status' => 'PENDING',
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'PENDING',
                ];
            } catch (Exception $e) {
                Log::error('Error preparing data:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);
                throw $e;
            }

            Log::info('Creating Transaction:', $transactionData);
            $transaction = Transaction::create($transactionData);

            // Load relationships
            $transaction->load([
                'user',
                'userLocation',
                'order' => function ($query) {
                    $query->with([
                        'orderItems' => function ($q) {
                            $q->with([
                                'product' => function ($p) {
                                    $p->with(['galleries', 'category']);
                                },
                                'merchant'
                            ]);
                        }
                    ]);
                }
            ]);

            DB::commit();

            Log::info('Transaction created successfully:', [
                'transaction_id' => $transaction->id,
                'order_id' => $order->id
            ]);

            return ResponseFormatter::success($transaction, 'Transaction created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ResponseFormatter::error(null, 'Failed to create transaction: ' . $e->getMessage(), 500);
        }
    }

    public function getByMerchant(Request $request, $merchantId)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $orderQuery = Order::with([
            'user:id,name,email,phone',
            'orderItems' => function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId)
                    ->with([
                        'product' => function ($p) {
                            $p->with(['galleries', 'category']);
                        }
                    ]);
            },
            'transaction' => function ($query) {
                $query->select('id', 'order_id', 'user_location_id', 'shipping_price', 'payment_status')
                    ->with(['userLocation']);
            }
        ])
            ->whereHas('orderItems', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            });

        // Filter by status if provided, but still include all orders
        if ($status) {
            $orderQuery->where('order_status', $status);
        }

        // Filter by date range if provided
        if ($startDate) {
            $orderQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $orderQuery->whereDate('created_at', '<=', $endDate);
        }

        // Order by latest first
        $orderQuery->latest();

        $orders = $orderQuery->paginate($limit);

        // Hitung total amount per merchant dari order items
        foreach ($orders as $order) {
            $order->merchant_total = $order->orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });
        }

        // Get all orders count by status
        $orderStatusCounts = Order::whereHas('orderItems', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
            ->select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return ResponseFormatter::success([
            'orders' => $orders,
            'status_counts' => $orderStatusCounts
        ], 'Merchant orders retrieved successfully');
    }

    public function updateOrderStatus(Request $request, $orderId, $merchantId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACCEPTED,REJECTED,PROCESSING,SHIPPED,DELIVERED,COMPLETED,CANCELED',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
        }

        DB::beginTransaction();
        try {
            // Verify the order belongs to the merchant
            $order = Order::whereHas('orderItems', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })->findOrFail($orderId);

            // Update order status
            $order->order_status = $request->status;
            if ($request->has('notes')) {
                $order->notes = $request->notes;
            }
            $order->save();

            // Update related transaction status if needed
            if (in_array($request->status, ['COMPLETED', 'CANCELED'])) {
                $order->transaction->update([
                    'status' => $request->status
                ]);
            }

            // Load updated order with relationships
            $order->load([
                'user:id,name,email,phone',
                'orderItems' => function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId)
                        ->with([
                            'product' => function ($p) {
                                $p->with(['galleries', 'category']);
                            }
                        ]);
                },
                'transaction' => function ($query) {
                    $query->with(['userLocation']);
                }
            ]);

            DB::commit();
            return ResponseFormatter::success($order, 'Order status updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to update order status: ' . $e->getMessage(), 500);
        }
    }

    public function getTransactionSummaryByMerchant(Request $request, $merchantId)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get base transaction query
        $transactionQuery = Transaction::whereHas('order.orderItems', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        });

        // Apply date filters if provided
        if ($startDate) {
            $transactionQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $transactionQuery->whereDate('created_at', '<=', $endDate);
        }

        // Get transactions with detailed information
        $transactions = $this->getByMerchant($request, $merchantId);

        // Calculate summary statistics
        $summary = [
            'total_sales' => $transactionQuery->sum('total_price'),
            'total_shipping' => $transactionQuery->sum('shipping_price'),
            'transaction_count' => $transactionQuery->count(),
            'status_breakdown' => $transactionQuery->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'payment_method_breakdown' => $transactionQuery->select('payment_method', DB::raw('count(*) as count'))
                ->groupBy('payment_method')
                ->pluck('count', 'payment_method'),
            'daily_transactions' => $transactionQuery->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(total_price) as total_amount')
            )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'transactions' => $transactions
        ];

        return ResponseFormatter::success($summary, 'Transaction summary retrieved successfully');
    }
}

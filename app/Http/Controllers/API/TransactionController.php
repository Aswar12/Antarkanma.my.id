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
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            // Find transaction and verify ownership
            $transaction = Transaction::with(['order.orderItems.product'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            // Check if transaction can be cancelled
            if ($transaction->status !== 'PENDING') {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak dapat dibatalkan',
                    422
                );
            }

            // Update transaction status
            $transaction->status = 'CANCELED';
            $transaction->save();

            // Update order status
            $transaction->order->order_status = 'CANCELED';
            $transaction->order->save();

            // Load relationships for response
            $transaction->load([
                'order.orderItems.product',
                'userLocation'
            ]);

            DB::commit();

            // Send notifications to merchants
            $merchantIds = $transaction->order->orderItems->pluck('merchant_id')->unique();

            foreach ($merchantIds as $merchantId) {
                $this->notificationController->sendTransactionCanceledNotification(
                    $merchantId,
                    $transaction->order->id,
                    $transaction->id
                );
            }

            return ResponseFormatter::success(
                $transaction,
                'Transaksi berhasil dibatalkan'
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel transaction:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak ditemukan',
                    404
                );
            }

            return ResponseFormatter::error(
                null,
                'Gagal membatalkan transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function list(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user',
                'userLocation',
                'order' => function ($query) {
                    $query->with([
                        'orderItems' => function ($q) {
                            $q->with([
                                'product' => function ($p) {
                                    $p->with(['galleries', 'category', 'variants']);
                                },
                                'variant',  // Add this to load the selected variant
                                'merchant'
                            ]);
                        }
                    ]);
                }
            ])
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
        try {
            Log::info('Transaction Create Request:', [
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            // Basic validation
            $validator = Validator::make($request->all(), [
                'user_location_id' => 'required|exists:user_locations,id',
                'total_price' => 'required|numeric|min:0',
                'shipping_price' => 'required|numeric|min:0',
                'payment_method' => 'required|in:MANUAL,ONLINE',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required',
                'items.*.product' => 'required|array',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.merchant' => 'required|array',
                'items.*.merchant.id' => 'required|exists:merchants,id'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
            }

            DB::beginTransaction();

            // Create Order
            $orderData = [
                'user_id' => Auth::id(),
                'total_amount' => (float) $request->total_price,
                'order_status' => 'PENDING',
            ];

            Log::info('Creating Order:', $orderData);
            $order = Order::create($orderData);

            // Create Order Items
            foreach ($request->items as $item) {
                // Ensure we have the product data
                $product = Product::find($item['product_id']);
                if (!$product) {
                    // If product doesn't exist in database, use the data from request
                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'merchant_id' => $item['merchant']['id'],
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $item['price'],
                    ];
                } else {
                    // If product exists, use database data
                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'merchant_id' => $product->merchant_id,
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $product->price,
                    ];
                }

                Log::info('Creating Order Item:', $orderItemData);
                OrderItem::create($orderItemData);
            }

            // Create Transaction
            $transactionData = [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'user_location_id' => $request->user_location_id,
                'total_price' => (float) $request->total_price,
                'shipping_price' => (float) $request->shipping_price,
                'status' => 'PENDING',
                'payment_method' => $request->payment_method,
                'payment_status' => 'PENDING',
            ];

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

            // Group items by merchant for notifications
            $merchantItems = collect($request->items)
                ->groupBy('merchant.id')
                ->map(function ($items) use ($order, $transaction) {
                    $merchantId = $items->first()['merchant']['id'];
                    
                    // Send notification to each merchant with their order details
                    $this->notificationController->sendNewTransactionNotification(
                        $merchantId,
                        $order->id,
                        $transaction->id
                    );
                });

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

            // Send notification to user
            $this->notificationController->sendOrderStatusUpdateNotification(
                $order->user->id,
                $order->id,
                $request->status
            );

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

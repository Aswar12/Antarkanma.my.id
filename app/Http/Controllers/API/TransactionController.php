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
    protected $firebaseService;

    public function __construct(
        NotificationController $notificationController,
        FirebaseService $firebaseService
    ) {
        $this->notificationController = $notificationController;
        $this->firebaseService = $firebaseService;
    }

    public function create(Request $request)
    {
        try {
            Log::info('Transaction Create Request:', [
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            // Basic validation - only accept product_id, variant_id, and quantity
            $validator = Validator::make($request->all(), [
                'user_location_id' => 'required|exists:user_locations,id',
                'payment_method' => 'required|in:MANUAL,ONLINE',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
            }

            DB::beginTransaction();

            // Calculate prices based on products and their variants
            $items = collect($request->items)->map(function ($item) {
                $product = Product::with(['merchant', 'variants'])->findOrFail($item['product_id']);
                
                // Get price based on variant if provided, otherwise use base product price
                $price = $product->price;
                $variant = null;
                
                if (!empty($item['variant_id'])) {
                    $variant = $product->variants->firstWhere('id', $item['variant_id']);
                    if ($variant) {
                        $price = $variant->price ?? $product->price;
                    }
                }
                
                return [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'merchant_id' => $product->merchant->id,
                    'subtotal' => $price * $item['quantity']
                ];
            });

            $total_price = $items->sum('subtotal');
            $shipping_price = 10000; // Default shipping price, can be adjusted based on your business logic

            // 1. Create Transaction first
            $transactionData = [
                'user_id' => Auth::id(),
                'user_location_id' => $request->user_location_id,
                'total_price' => $total_price,
                'shipping_price' => $shipping_price,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'courier_approval' => Transaction::COURIER_PENDING,
                'timeout_at' => now()->addMinutes(5)
            ];

            Log::info('Creating Transaction:', $transactionData);
            $transaction = Transaction::create($transactionData);

            // 2. Group items by merchant
            $itemsByMerchant = $items->groupBy('merchant_id');

            // 3. Create Order for each merchant
            foreach ($itemsByMerchant as $merchantId => $merchantItems) {
                // Calculate merchant subtotal
                $merchantSubtotal = $merchantItems->sum(function ($item) {
                    return $item['quantity'] * $item['price'];
                });

                // Create Order
                $orderData = [
                    'transaction_id' => $transaction->id,
                    'user_id' => Auth::id(),
                    'merchant_id' => $merchantId,
                    'total_amount' => $merchantSubtotal,
                    'order_status' => Order::STATUS_PENDING,
                    'merchant_approval' => Order::MERCHANT_PENDING
                ];

                Log::info('Creating Order:', $orderData);
                $order = Order::create($orderData);

                // Create OrderItems for this merchant
                foreach ($merchantItems as $item) {
                    $product = Product::find($item['product_id']);
                    
                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['variant_id'], // Changed from variant_id to product_variant_id
                        'merchant_id' => $merchantId,
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $item['price']
                    ];

                    Log::info('Creating Order Item:', $orderItemData);
                    OrderItem::create($orderItemData);
                }

                // Send notification to merchant
                $this->notificationController->sendNewTransactionNotification(
                    $merchantId,
                    $order->id,
                    $transaction->id
                );
            }

            // Load relationships
            $transaction->load([
                'user',
                'userLocation',
                'orders' => function ($query) {
                    $query->with([
                        'orderItems' => function ($q) {
                            $q->with([
                                'product' => function ($p) {
                                    $p->with(['galleries', 'category', 'variants']);
                                },
                                'merchant',
                                'variant'
                            ]);
                        }
                    ]);
                }
            ]);

            DB::commit();

            Log::info('Transaction created successfully:', [
                'transaction_id' => $transaction->id,
                'orders' => $transaction->orders->pluck('id')
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

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            // Find transaction and verify ownership
            $transaction = Transaction::with(['orders.orderItems.product'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            // Check if transaction can be cancelled
            if ($transaction->status !== Transaction::STATUS_PENDING) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak dapat dibatalkan',
                    422
                );
            }

            // Update transaction status
            $transaction->status = Transaction::STATUS_CANCELED;
            $transaction->save();

            // Update all orders status
            foreach ($transaction->orders as $order) {
                $order->order_status = Order::STATUS_CANCELED;
                $order->save();

                // Notify merchant
                $this->notificationController->sendTransactionCanceledNotification(
                    $order->merchant_id,
                    $order->id,
                    $transaction->id
                );
            }

            DB::commit();

            return ResponseFormatter::success(
                $transaction->load([
                    'orders.orderItems' => function ($query) {
                        $query->with([
                            'product' => function ($p) {
                                $p->with(['galleries', 'category', 'variants']);
                            },
                            'variant',
                            'merchant'
                        ]);
                    },
                    'userLocation'
                ]),
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
                'orders' => function ($query) {
                    $query->with([
                        'orderItems' => function ($q) {
                            $q->with([
                                'product' => function ($p) {
                                    $p->with(['galleries', 'category', 'variants']);
                                },
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

    public function getByMerchant(Request $request, $merchantId)
    {
        $limit = $request->input('limit', 10);
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $orderQuery = Order::with([
            'transaction',
            'user:id,name,email,phone',
            'orderItems' => function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId)
                    ->with([
                        'product' => function ($p) {
                            $p->with(['galleries', 'category', 'variants']);
                        },
                        'variant'
                    ]);
            }
        ])
            ->where('merchant_id', $merchantId);

        // Filter by status if provided
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

        // Calculate total amount for each order
        foreach ($orders as $order) {
            $order->total = $order->orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });
        }

        // Get orders count by status
        $orderStatusCounts = Order::where('merchant_id', $merchantId)
            ->select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return ResponseFormatter::success([
            'orders' => $orders,
            'status_counts' => $orderStatusCounts
        ], 'Merchant orders retrieved successfully');
    }

    public function getTransactionSummaryByMerchant(Request $request, $merchantId)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get orders for this merchant
        $orderQuery = Order::where('merchant_id', $merchantId)
            ->with(['orderItems', 'transaction']);

        // Apply date filters if provided
        if ($startDate) {
            $orderQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $orderQuery->whereDate('created_at', '<=', $endDate);
        }

        $orders = $orderQuery->get();

        // Calculate summary statistics
        $summary = [
            'total_sales' => $orders->sum(function ($order) {
                return $order->orderItems->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            }),
            'total_orders' => $orders->count(),
            'status_breakdown' => $orders->groupBy('order_status')
                ->map(function ($group) {
                    return $group->count();
                }),
            'payment_method_breakdown' => $orders->groupBy('transaction.payment_method')
                ->map(function ($group) {
                    return $group->count();
                }),
            'daily_orders' => $orders->groupBy(function ($order) {
                return $order->created_at->format('Y-m-d');
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum(function ($order) {
                        return $order->orderItems->sum(function ($item) {
                            return $item->quantity * $item->price;
                        });
                    })
                ];
            })
        ];

        return ResponseFormatter::success($summary, 'Transaction summary retrieved successfully');
    }
}

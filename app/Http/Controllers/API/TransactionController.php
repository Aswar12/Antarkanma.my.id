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
use App\Services\OsrmService;
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

    private function itemsMatchShippingCalculation($requestItems, $shippingItems)
    {
        if (count($requestItems) !== count($shippingItems)) {
            return false;
        }

        // Sort both arrays by product_id to ensure consistent comparison
        $sortedRequestItems = collect($requestItems)->sortBy('product_id')->values()->toArray();
        $sortedShippingItems = collect($shippingItems)->sortBy('product_id')->values()->toArray();

        foreach ($sortedRequestItems as $index => $requestItem) {
            $shippingItem = $sortedShippingItems[$index];

            // Check if product_id and quantity match, ignore merchant_id
            if ($requestItem['product_id'] != $shippingItem['product_id'] ||
                $requestItem['quantity'] != $shippingItem['quantity']) {
                Log::info('Items mismatch:', [
                    'request_item' => $requestItem,
                    'shipping_item' => $shippingItem
                ]);
                return false;
            }
        }

        return true;
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
                'payment_method' => 'required|in:MANUAL,ONLINE',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.customer_note' => 'nullable|string|max:500'
            ]);

            Log::info('Validating transaction request:', [
                'user_id' => Auth::id(),
                'items_count' => count($request->items)
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors(), 'Validation error', 422);
            }

            DB::beginTransaction();

            // Get shipping calculation from database cache
            $cacheKey = 'shipping_calculation_' . Auth::id();
            $cacheData = DB::table('cache')
                ->where('key', $cacheKey)
                ->where('expiration', '>', now()->timestamp)
                ->first();

            if (!$cacheData) {
                Log::error('No shipping calculation found in cache:', [
                    'key' => $cacheKey,
                    'user_id' => Auth::id()
                ]);
                return ResponseFormatter::error(
                    null,
                    'Please calculate shipping first by calling /shipping/preview endpoint',
                    422
                );
            }

            $shippingCalculation = unserialize($cacheData->value);

            Log::info('Retrieved shipping calculation from cache:', [
                'key' => $cacheKey,
                'user_id' => Auth::id(),
                'request_items' => $request->items,
                'shipping_items' => $shippingCalculation['items'],
                'calculation_exists' => true,
                'expires_at' => date('Y-m-d H:i:s', $cacheData->expiration),
                'shipping_data' => $shippingCalculation,
                'cache_value' => $cacheData->value,
                'cache_expiration' => $cacheData->expiration,
                'current_timestamp' => now()->timestamp,
                'cache_key_used' => $cacheKey,
                'auth_id' => Auth::id()
            ]);

            Log::info('Comparing items:', [
                'request_items_count' => count($request->items),
                'shipping_items_count' => count($shippingCalculation['items']),
                'request_items' => collect($request->items)->map(function($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity']
                    ];
                })->toArray(),
                'shipping_items' => collect($shippingCalculation['items'])->map(function($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity']
                    ];
                })->toArray(),
                'shipping_calculation_structure' => array_keys($shippingCalculation),
                'shipping_data_structure' => array_keys($shippingCalculation['data'] ?? [])
            ]);

            // Check if shipping preview has been calculated
            if (!$shippingCalculation) {
                Log::error('No shipping calculation found:', [
                    'user_id' => Auth::id(),
                    'request_items' => $request->items
                ]);
                return ResponseFormatter::error(
                    null,
                    'Please calculate shipping first by calling /shipping/preview endpoint',
                    422
                );
            }


            // Check if items match
            if (!isset($shippingCalculation['items'])) {
                Log::error('Shipping calculation missing items:', [
                    'user_id' => Auth::id(),
                    'cache_data' => $shippingCalculation
                ]);
                return ResponseFormatter::error(
                    null,
                    'Invalid shipping calculation. Please recalculate shipping.',
                    422
                );
            }

            // Verify items match the shipping calculation
            if (!$this->itemsMatchShippingCalculation($request->items, $shippingCalculation['items'])) {
                return ResponseFormatter::error(
                    null,
                    'Cart items have changed. Please recalculate shipping.',
                    422
                );
            }

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
                    'subtotal' => $price * $item['quantity'],
                    'customer_note' => $item['customer_note'] ?? null
                ];
            });

            $total_price = $items->sum('subtotal');
            $total_shipping_price = $shippingCalculation['data']['total_shipping_price'];
            $shippingDetails = $shippingCalculation['data']['merchant_deliveries'];

            // Group items by merchant
            $itemsByMerchant = $items->groupBy('merchant_id');

            // Get base merchant from shipping calculation
            $baseMerchant = collect($shippingDetails)
                ->where('route_type', 'base_merchant')
                ->first();

            if (!$baseMerchant) {
                Log::error('No base merchant found in shipping details', [
                    'shipping_details' => $shippingDetails
                ]);
                return ResponseFormatter::error(
                    null,
                    'Invalid shipping calculation. Please recalculate shipping.',
                    422
                );
            }

            // Create Transaction
            $transactionData = [
                'user_id' => Auth::id(),
                'user_location_id' => $request->user_location_id,
                'total_price' => $total_price,
                'shipping_price' => $total_shipping_price,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'courier_approval' => Transaction::COURIER_PENDING,
                'timeout_at' => now()->addMinutes(10), // Set 10 minutes timeout
                'base_merchant_id' => $baseMerchant['merchant_id']
            ];

            Log::info('Creating Transaction:', $transactionData);
            $transaction = Transaction::create($transactionData);

            // Check and cancel any timed out transactions
            $timedOutTransactions = Transaction::where('status', Transaction::STATUS_PENDING)
                ->where('courier_approval', Transaction::COURIER_PENDING)
                ->where('timeout_at', '<', now())
                ->get();

            foreach ($timedOutTransactions as $timedOutTransaction) {
                $timedOutTransaction->status = Transaction::STATUS_CANCELED;
                $timedOutTransaction->save();

                // Cancel all associated orders
                $timedOutTransaction->orders()->update([
                    'order_status' => 'CANCELED'
                ]);

                Log::info("Transaction {$timedOutTransaction->id} automatically canceled due to timeout");
            }

            // Kirim notifikasi ke semua kurir melalui topic
            $this->firebaseService->sendNotification(
                'new_transactions',
                'Orderan Baru Tersedia!',
                'Ada orderan baru yang menunggu untuk diambil'
            );


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
                        'price' => (float) $item['price'],
                        'customer_note' => $item['customer_note'] ?? null
                    ];

                    Log::info('Creating Order Item:', $orderItemData);
                    OrderItem::create($orderItemData);
                }

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

            // Add shipping details to response with rounded total
            $responseData = $transaction->toArray();
            $responseData['shipping_details'] = [
                'total_shipping_price' => $total_shipping_price,
                'merchant_deliveries' => collect($shippingDetails)->map(function($detail, $merchantId) {
                    return array_merge(['merchant_id' => $merchantId], $detail);
                })->values()->toArray()
            ];

            Log::info('Final shipping details:', [
                'total_shipping_price' => $total_shipping_price,
                'merchant_count' => count($shippingDetails),
                'breakdown' => collect($shippingDetails)->map(function($detail) {
                    return [
                        'cost' => $detail['cost'],
                        'distance' => $detail['distance'],
                        'is_additional' => $detail['is_additional_distance'] ?? false // Default to false if not set
                    ];
                })
            ]);

            Log::info('Transaction created successfully:', [
                'transaction_id' => $transaction->id,
                'orders' => $transaction->orders->pluck('id'),
                'shipping_details' => $responseData['shipping_details']
            ]);

            // Keep shipping calculation in cache for courier assignment
            // Cache will be cleared when transaction is completed or canceled

            return ResponseFormatter::success($responseData, 'Transaction created successfully');
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

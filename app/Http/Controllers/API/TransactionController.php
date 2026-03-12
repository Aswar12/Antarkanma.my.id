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
use App\Models\User;
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
    protected $osrmService;

    public function __construct(
        NotificationController $notificationController,
        FirebaseService $firebaseService,
        OsrmService $osrmService
    ) {
        $this->notificationController = $notificationController;
        $this->firebaseService = $firebaseService;
        $this->osrmService = $osrmService;
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

            // Check if product_id, variant_id, and quantity match, ignore merchant_id
            $variantMatch = true;
            if (isset($requestItem['variant_id']) || isset($shippingItem['variant_id'])) {
                $reqVariant = $requestItem['variant_id'] ?? null;
                $shipVariant = $shippingItem['variant_id'] ?? null;
                $variantMatch = ($reqVariant == $shipVariant);
            }

            if ($requestItem['product_id'] != $shippingItem['product_id'] ||
                $requestItem['quantity'] != $shippingItem['quantity'] ||
                !$variantMatch
            ) {
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
                'payment_method' => 'required|in:COD,QRIS_DUAL,QRIS_PLATFORM,MANUAL,ONLINE',
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

            // Fetch user location
            $userLocation = UserLocation::find($request->user_location_id);
            if (!$userLocation) {
                return ResponseFormatter::error(null, 'User location not found', 404);
            }

            // Real-time dynamic shipping calculation based on actual request items
            // This prevents cache manipulation or timeout issues during checkout
            Log::info('Calculating shipping costs on-the-fly for checkout:', [
                'user_id' => Auth::id(),
                'items_count' => count($request->items)
            ]);
            $shippingCalculation = ['data' => $this->osrmService->calculateCompleteShipping($userLocation, $request->items)];


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
            $base_shipping_price = $shippingCalculation['data']['base_shipping_price'] ?? $total_shipping_price;
            $service_fee = $shippingCalculation['data']['service_fee'] ?? 500; // Default Rp 500 per TRANSACTION
            // IMPORTANT: Service fee is charged ONCE per transaction, not per order
            $platform_fee = $base_shipping_price * 0.10; // 10% of base shipping
            $courier_earning = $base_shipping_price - $platform_fee;
            $shippingDetails = $shippingCalculation['data']['merchant_deliveries'];

            // Dual QRIS: Split payment amounts
            $merchant_amount = $total_price; // Customer pays merchant directly via QRIS
            $platform_amount = $base_shipping_price + $service_fee; // Customer pays platform via QRIS
            $grand_total = $merchant_amount + $platform_amount;

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
                'merchant_amount' => $merchant_amount,
                'platform_amount' => $platform_amount,
                'grand_total' => $grand_total,
                'shipping_price' => $total_shipping_price,
                'base_shipping_price' => $base_shipping_price,
                'service_fee' => $service_fee,
                'platform_fee' => $platform_fee,
                'courier_earning' => $courier_earning,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'courier_approval' => Transaction::COURIER_PENDING,
                'timeout_at' => now()->addMinutes(120), // Set 2 hours timeout (Hybrid System)
                'base_merchant_id' => $baseMerchant['merchant_id'],
            ];
            
            // ─────────────────────────────────────────────────
            // MULTI-MERCHANT DETECTION
            // ─────────────────────────────────────────────────
            $merchantCount = $itemsByMerchant->keys()->count();
            
            if ($merchantCount > 1) {
                // Multi-merchant order
                Log::info('Multi-merchant order detected', [
                    'merchant_count' => $merchantCount,
                    'merchants' => $itemsByMerchant->keys()->toArray(),
                ]);
                
                // For MVP: Recommend separate transactions
                // Future: Use PLATFORM_QRIS single payment
                
                // Add warning to response
                $multiMerchantWarning = 'Pesanan Anda terdiri dari ' . $merchantCount . ' merchant berbeda. ' .
                                        'Untuk saat ini, silahkan buat pesanan terpisah untuk setiap merchant ' .
                                        'agar pembayaran lebih mudah (2x scan per merchant). ' .
                                        'Fitur pembayaran tunggal untuk multi-merchant akan segera hadir!';
            } else {
                $multiMerchantWarning = null;
            }
            
            // Add QRIS URLs for dual payment
            if ($request->payment_method === 'QRIS_DUAL') {
                // Get merchant QRIS URL
                $baseMerchantModel = Merchant::find($baseMerchant['merchant_id']);
                $transactionData['merchant_qris_url'] = $baseMerchantModel->qris_url;
                $transactionData['platform_qris_url'] = config('services.platform.qris_url'); // Platform QRIS
            }

            Log::info('Creating Transaction:', $transactionData);
            $transaction = Transaction::create($transactionData);

            // Auto-cancel timeout DIMATIKAN untuk Hybrid System
            // Order tidak akan di-cancel meskipun timeout

            // Broadcast ke semua kurir
            // Kurir akan mendapatkan pesanan melalui mekanisme polling 15-detik.

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
                    'order_status' => Order::STATUS_WAITING_APPROVAL, // Auto-set to WAITING_APPROVAL
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

                // Send Firebase Notification to this specific Merchant owner
                $merchant = Merchant::with('owner.fcmTokens')->find($merchantId);
                if ($merchant && $merchant->owner) {
                    $merchantTokens = $merchant->owner->fcmTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->toArray();

                    if (!empty($merchantTokens)) {
                        $this->firebaseService->sendToUser(
                            $merchantTokens,
                            [
                                'type' => 'new_order',
                                'transaction_id' => $transaction->id,
                                'order_id' => $order->id,
                            ],
                            'Pesanan Baru Masuk!',
                            "Ada pesanan baru menanti konfirmasi Anda untuk segera diproses."
                        );
                        Log::info("Notification new_order sent to Merchant ID: {$merchantId}");
                    }

                    // Create inbox notification for merchant
                    NotificationController::createInboxNotification(
                        $merchant->owner,
                        'new_order',
                        '📦 Pesanan Baru!',
                        "Pesanan baru #" . $order->id . " menunggu konfirmasi Anda.",
                        [
                            'order_id' => $order->id,
                            'transaction_id' => $transaction->id,
                        ]
                    );
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
                'base_shipping_price' => $base_shipping_price,
                'service_fee' => $service_fee,
                'platform_fee' => $platform_fee,
                'courier_earning' => $courier_earning,
                'merchant_deliveries' => collect($shippingDetails)->map(function($detail, $merchantId) {
                    return array_merge(['merchant_id' => $merchantId], $detail);
                })->values()->toArray()
            ];
            
            // Add dual QRIS payment info
            if ($transaction->payment_method === 'QRIS_DUAL') {
                $responseData['payment_info'] = [
                    'payment_type' => 'DUAL_QRIS',
                    'payments' => [
                        [
                            'type' => 'MERCHANT_QRIS',
                            'amount' => $merchant_amount,
                            'description' => 'Pembayaran makanan ke merchant',
                            'qris_url' => $transaction->merchant_qris_url,
                            'merchant_name' => $baseMerchantModel->name,
                        ],
                        [
                            'type' => 'PLATFORM_QRIS',
                            'amount' => $platform_amount,
                            'description' => 'Ongkir + Service Fee ke platform',
                            'qris_url' => $transaction->platform_qris_url,
                            'breakdown' => [
                                'base_ongkir' => $base_shipping_price,
                                'service_fee' => $service_fee,
                            ]
                        ],
                    ],
                    'instructions' => [
                        '1. Scan QRIS Merchant untuk bayar makanan sebesar Rp ' . number_format($merchant_amount, 0, ',', '.'),
                        '2. Scan QRIS Platform untuk bayar ongkir sebesar Rp ' . number_format($platform_amount, 0, ',', '.'),
                        '3. Upload bukti pembayaran kedua QRIS di halaman detail transaksi',
                        '4. Pesanan akan diproses setelah kedua pembayaran terkonfirmasi',
                    ],
                    'grand_total' => $grand_total,
                ];
            } elseif ($transaction->payment_method === 'COD') {
                $responseData['payment_info'] = [
                    'payment_type' => 'COD',
                    'amount_due' => $grand_total,
                    'instructions' => [
                        'Bayar Rp ' . number_format($grand_total, 0, ',', '.') . ' saat pesanan tiba di lokasi Anda',
                        'Pastikan menyiapkan uang pas atau uang kecil untuk memudahkan kurir',
                    ],
                ];
            }

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
            
            // Add multi-merchant warning if applicable
            if ($multiMerchantWarning) {
                $responseData['multi_merchant_warning'] = $multiMerchantWarning;
                $responseData['recommendation'] = 'Buat pesanan terpisah untuk setiap merchant untuk pembayaran yang lebih mudah.';
            }

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

    /**
     * Get transaction detail by ID
     */
    public function get($id)
    {
        try {
            $transaction = Transaction::with([
                'user',
                'userLocation',
                'courier.user',
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
                ->findOrFail($id);

            // Transform response
            $responseData = $transaction->toArray();

            // Add courier details if exists
            if ($transaction->courier) {
                $responseData['courier'] = [
                    'id' => $transaction->courier->id,
                    'name' => $transaction->courier->user->name,
                    'phone' => $transaction->courier->user->phone_number,
                    'vehicle_type' => $transaction->courier->vehicle_type,
                    'license_plate' => $transaction->courier->license_plate,
                    'photo' => $transaction->courier->user->profile_photo_url
                ];
            }

            // TOMBOL CHAT MUNCUL JIKA: courier_id SUDAH ADA (tidak null)
            // Ini berarti kurir sudah mengambil order dan siap dihubungi
            $responseData['can_chat_with_courier'] = !is_null($transaction->courier_id);

            return ResponseFormatter::success(
                $responseData,
                'Detail transaksi berhasil diambil'
            );
        } catch (Exception $error) {
            Log::error('Failed to fetch transaction detail:', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);

            if ($error instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak ditemukan',
                    404
                );
            }

            return ResponseFormatter::error(
                null,
                'Data transaksi gagal diambil: ' . $error->getMessage(),
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
                'courier.user', // Include courier and their user info
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

            // Transform response: add can_chat_with_courier flag to each transaction
            $transactionsData = $transactions->map(function ($transaction) {
                $data = $transaction->toArray();
                
                // Add courier details if exists
                if ($transaction->courier) {
                    $data['courier'] = [
                        'id' => $transaction->courier->id,
                        'name' => $transaction->courier->user->name,
                        'phone' => $transaction->courier->user->phone_number,
                        'vehicle_type' => $transaction->courier->vehicle_type,
                        'license_plate' => $transaction->courier->license_plate,
                        'photo' => $transaction->courier->user->profile_photo_url
                    ];
                }

                // TOMBOL CHAT MUNCUL JIKA: courier_id SUDAH ADA (tidak null)
                $data['can_chat_with_courier'] = !is_null($transaction->courier_id);

                return $data;
            });

            return ResponseFormatter::success(
                $transactionsData,
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

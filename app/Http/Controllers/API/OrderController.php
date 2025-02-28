<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserLocation;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function markOrderAsReady(Request $request, $orderId)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id'
            ]);

            $order = Order::with([
                'orderItems:id,order_id,product_id,quantity,price',
                'orderItems.product:id,name,price,status',
                'transaction:id,user_id,courier_id,status,payment_method,payment_status',
                'transaction.user:id,name,phone_number,profile_photo_path',
                'transaction.courier:id,user_id,vehicle_type,license_plate',
                'transaction.courier.user:id,name,phone_number,profile_photo_path'
            ])->findOrFail($orderId);

            // Verify merchant ownership
            if ($order->merchant_id !== $request->merchant_id) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: Order does not belong to this merchant',
                    403
                );
            }

            // Verify order can be marked as ready
            if ($order->order_status !== Order::STATUS_PROCESSING) {
                return ResponseFormatter::error(
                    null,
                    'Order cannot be marked as ready: Invalid status',
                    400
                );
            }

            DB::beginTransaction();
            try {
                // Update order status
                $order->order_status = Order::STATUS_READY;
                $order->save();

                // Send notifications
                if ($this->firebaseService) {
                    // Notify courier
                    if ($order->transaction && $order->transaction->courier_id) {
                        $this->firebaseService->sendNotification(
                            'user_' . $order->transaction->courier->user_id,
                            'Pesanan Siap Diambil',
                            'Pesanan #' . $order->id . ' sudah siap untuk diambil di merchant',
                            [
                                'type' => 'order_ready_for_pickup',
                                'order_id' => $order->id,
                                'transaction_id' => $order->transaction->id,
                                'merchant_name' => $order->merchant->name,
                                'merchant_address' => $order->merchant->address
                            ]
                        );
                    }

                    // Notify user
                    $this->firebaseService->sendNotification(
                        'user_' . $order->user_id,
                        'Pesanan Siap',
                        'Pesanan #' . $order->id . ' sudah siap dan akan segera diambil oleh kurir',
                        [
                            'type' => 'order_ready',
                            'order_id' => $order->id,
                            'transaction_id' => $order->transaction->id
                        ]
                    );
                }

                DB::commit();

                // Transform response data
                $orderArray = $order->toArray();

                // Simplify order items
                $orderArray['items'] = collect($order->orderItems)->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => [
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'image' => $item->product->galleries[0]->url ?? null
                        ]
                    ];
                });

                // Simplify transaction info
                $orderArray['transaction'] = [
                    'status' => $order->transaction->status,
                    'payment_method' => $order->transaction->payment_method,
                    'payment_status' => $order->transaction->payment_status
                ];

                // Add customer info with photo
                $orderArray['customer'] = [
                    'name' => $order->transaction->user->name,
                    'phone' => $order->transaction->user->phone_number,
                    'photo' => $order->transaction->user->profile_photo_url
                ];

                // Add courier info if exists
                if ($order->transaction->courier) {
                    $orderArray['courier'] = [
                        'name' => $order->transaction->courier->user->name,
                        'phone' => $order->transaction->courier->user->phone_number,
                        'vehicle' => $order->transaction->courier->vehicle_type,
                        'plate' => $order->transaction->courier->license_plate,
                        'photo' => $order->transaction->courier->user->profile_photo_url
                    ];
                }

                // Calculate total
                $orderArray['total'] = $order->orderItems->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                // Remove redundant data
                unset($orderArray['orderItems']);
                unset($orderArray['user_id']);
                unset($orderArray['merchant_id']);

                return ResponseFormatter::success(
                    $orderArray,
                    'Order marked as ready successfully'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to mark order as ready: ' . $e->getMessage(),
                500
            );
        }
    }

    public function updateMerchantApproval(Request $request, $orderId)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id',
                'is_approved' => 'required|boolean',
                'reason' => 'required_if:is_approved,false|string|max:255'
            ]);

            $order = Order::with([
                'orderItems:id,order_id,product_id,quantity,price',
                'orderItems.product:id,name,price,status',
                'transaction:id,user_id,courier_id,status,payment_method,payment_status',
                'transaction.user:id,name,phone_number,profile_photo_path',
                'transaction.courier:id,user_id,vehicle_type,license_plate',
                'transaction.courier.user:id,name,phone_number,profile_photo_path'
            ])->findOrFail($orderId);

            // Verify merchant ownership
            if ($order->merchant_id !== $request->merchant_id) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: Order does not belong to this merchant',
                    403
                );
            }

            // Verify order can be approved/rejected
            if ($order->order_status !== Order::STATUS_WAITING_APPROVAL) {
                return ResponseFormatter::error(
                    null,
                    'Order cannot be ' . ($request->is_approved ? 'approved' : 'rejected') . ': Invalid status',
                    400
                );
            }

            DB::beginTransaction();
            try {
                // Update order status and merchant approval
                if ($request->is_approved) {
                    $order->order_status = Order::STATUS_PROCESSING;
                    $order->merchant_approval = Order::MERCHANT_APPROVED;
                } else {
                    $order->order_status = Order::STATUS_CANCELED;
                    $order->merchant_approval = Order::MERCHANT_REJECTED;
                    $order->rejection_reason = $request->reason;

                    // Check if all orders in transaction are canceled
                    $transaction = $order->transaction;
                    if ($transaction->allOrdersCanceled()) {
                        $transaction->status = Transaction::STATUS_CANCELED;
                        $transaction->save();
                    }
                }
                $order->save();

                // Send notifications
                if ($this->firebaseService) {
                    // Notify user
                    $this->firebaseService->sendNotification(
                        'user_' . $order->user_id,
                        $request->is_approved ? 'Pesanan Disetujui' : 'Pesanan Ditolak',
                        $request->is_approved
                            ? 'Pesanan #' . $order->id . ' telah disetujui dan sedang diproses'
                            : 'Pesanan #' . $order->id . ' ditolak: ' . $request->reason,
                        [
                            'type' => $request->is_approved ? 'order_approved' : 'order_rejected',
                            'order_id' => $order->id,
                            'transaction_id' => $order->transaction->id,
                            'reason' => $request->reason ?? null
                        ]
                    );

                    // Notify courier if assigned
                    if ($order->transaction && $order->transaction->courier_id) {
                        $this->firebaseService->sendNotification(
                            'user_' . $order->transaction->courier->user_id,
                            'Status Pesanan Diperbarui',
                            $request->is_approved
                                ? 'Pesanan #' . $order->id . ' telah disetujui oleh merchant'
                                : 'Pesanan #' . $order->id . ' telah ditolak oleh merchant',
                            [
                                'type' => $request->is_approved ? 'merchant_approved_order' : 'merchant_rejected_order',
                                'order_id' => $order->id,
                                'transaction_id' => $order->transaction->id,
                                'reason' => $request->reason ?? null
                            ]
                        );
                    }
                }

                DB::commit();

                // Transform response data
                $orderArray = $order->toArray();

                // Simplify order items
                $orderArray['items'] = collect($order->orderItems)->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => [
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'image' => $item->product->galleries[0]->url ?? null
                        ]
                    ];
                });

                // Simplify transaction info
                $orderArray['transaction'] = [
                    'status' => $order->transaction->status,
                    'payment_method' => $order->transaction->payment_method,
                    'payment_status' => $order->transaction->payment_status
                ];

                // Add customer info with photo
                $orderArray['customer'] = [
                    'name' => $order->transaction->user->name,
                    'phone' => $order->transaction->user->phone_number,
                    'photo' => $order->transaction->user->profile_photo_url
                ];

                // Add courier info if exists
                if ($order->transaction->courier) {
                    $orderArray['courier'] = [
                        'name' => $order->transaction->courier->user->name,
                        'phone' => $order->transaction->courier->user->phone_number,
                        'vehicle' => $order->transaction->courier->vehicle_type,
                        'plate' => $order->transaction->courier->license_plate,
                        'photo' => $order->transaction->courier->user->profile_photo_url
                    ];
                }

                // Calculate total
                $orderArray['total'] = $order->orderItems->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                // Remove redundant data
                unset($orderArray['orderItems']);
                unset($orderArray['user_id']);
                unset($orderArray['merchant_id']);

                return ResponseFormatter::success(
                    $orderArray,
                    'Order ' . ($request->is_approved ? 'approved' : 'rejected') . ' successfully'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to ' . ($request->is_approved ? 'approve' : 'reject') . ' order: ' . $e->getMessage(),
                500
            );
        }
    }

    public function approveOrder(Request $request, $orderId)
    {
        $request->merge([
            'is_approved' => true
        ]);
        return $this->updateMerchantApproval($request, $orderId);
    }

    public function rejectOrder(Request $request, $orderId)
    {
        $request->merge([
            'is_approved' => false
        ]);
        return $this->updateMerchantApproval($request, $orderId);
    }

    public function getByMerchant(Request $request, $merchantId)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            // Base query with optimized eager loading
            $query = Order::select('id', 'transaction_id', 'total_amount', 'order_status', 'merchant_approval', 'created_at')
            ->with([
                'orderItems:id,order_id,product_id,quantity,price',
                'orderItems.product:id,name,price,status',
                'orderItems.product.galleries:id,product_id,url',
                'transaction:id,user_id,courier_id,status,payment_method,payment_status',
                'transaction.user:id,name,phone_number,profile_photo_path',
                'transaction.courier:id,user_id,vehicle_type,license_plate',
                'transaction.courier.user:id,name,phone_number,profile_photo_path'
            ])
            ->where('merchant_id', $merchantId)
            ->where(function($q) {
                $q->where('order_status', Order::STATUS_WAITING_APPROVAL)
                  ->orWhere('order_status', '!=', Order::STATUS_PENDING);
            })
            ->whereHas('transaction', function($q) {
                $q->where('courier_approval', Transaction::COURIER_APPROVED);
            });

            // Apply filters
            if ($status) {
                $query->where('order_status', $status);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', Carbon::parse($startDate));
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', Carbon::parse($endDate));
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderItems.product', function($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated orders with calculated totals
            $orders = $query->paginate($perPage);

            // Transform response data to remove redundant information
            $orders->through(function ($order) {
                $orderArray = $order->toArray();

                // Simplify order items
                $orderArray['items'] = collect($order->orderItems)->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => [
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'image' => $item->product->galleries[0]->url ?? null
                        ]
                    ];
                });

                // Simplify transaction info
                $orderArray['transaction'] = [
                    'status' => $order->transaction->status,
                    'payment_method' => $order->transaction->payment_method,
                    'payment_status' => $order->transaction->payment_status
                ];

                // Add customer info with photo
                $orderArray['customer'] = [
                    'name' => $order->transaction->user->name,
                    'phone' => $order->transaction->user->phone_number,
                    'photo' => $order->transaction->user->profile_photo_url
                ];

                // Add courier info with photo if exists
                if ($order->transaction->courier) {
                    $orderArray['courier'] = [
                        'name' => $order->transaction->courier->user->name,
                        'phone' => $order->transaction->courier->user->phone_number,
                        'vehicle' => $order->transaction->courier->vehicle_type,
                        'plate' => $order->transaction->courier->license_plate,
                        'photo' => $order->transaction->courier->user->profile_photo_url
                    ];
                }

                // Calculate total
                $orderArray['total'] = $order->orderItems->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                // Remove redundant data
                unset($orderArray['orderItems']);
                unset($orderArray['user_id']);
                unset($orderArray['merchant_id']);

                return $orderArray;
            });

            // Get status counts with date and search filters
            $statusCountsQuery = Order::where('merchant_id', $merchantId)
                ->where(function($q) {
                    $q->where('order_status', Order::STATUS_WAITING_APPROVAL)
                      ->orWhere('order_status', '!=', Order::STATUS_PENDING);
                })
                ->whereHas('transaction', function($q) {
                    $q->where('courier_approval', Transaction::COURIER_APPROVED);
                });

            if ($startDate) {
                $statusCountsQuery->whereDate('created_at', '>=', Carbon::parse($startDate));
            }

            if ($endDate) {
                $statusCountsQuery->whereDate('created_at', '<=', Carbon::parse($endDate));
            }

            if ($search) {
                $statusCountsQuery->where(function($q) use ($search) {
                    $q->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderItems.product', function($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
                });
            }

            $statusCounts = $statusCountsQuery
                ->select('order_status', DB::raw('count(*) as total'))
                ->groupBy('order_status')
                ->pluck('total', 'order_status')
                ->toArray();

            // Ensure all statuses have a count
            $allStatuses = [
                Order::STATUS_PENDING,
                Order::STATUS_WAITING_APPROVAL,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
                Order::STATUS_PICKED_UP,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELED
            ];

            foreach ($allStatuses as $status) {
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
            }

            // Get summary statistics
            $summary = [
                'total_orders' => array_sum($statusCounts),
                'total_completed' => $statusCounts[Order::STATUS_COMPLETED] ?? 0,
                'total_processing' => $statusCounts[Order::STATUS_PROCESSING] ?? 0,
                'total_pending' => $statusCounts[Order::STATUS_PENDING] ?? 0,
                'total_canceled' => $statusCounts[Order::STATUS_CANCELED] ?? 0,
            ];

            return ResponseFormatter::success([
                'orders' => $orders,
                'status_counts' => $statusCounts,
                'summary' => $summary,
                'filters' => [
                    'status' => $status,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'search' => $search,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ], 'Merchant orders retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to retrieve merchant orders: ' . $e->getMessage(),
                500
            );
        }
    }
}

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

    public function getByMerchant($merchantId, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            // Base query with eager loading
            $query = Order::with([
                'orderItems' => function ($query) {
                    $query->with([
                        'product' => function ($q) {
                            $q->with(['galleries', 'category', 'variants']);
                        }
                    ]);
                },
                'transaction',
                'user:id,name,email,phone'
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
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderItems.product', function($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated orders
            $orders = $query->paginate($perPage);

            // Calculate total amount for each order
            foreach ($orders as $order) {
                $order->total = $order->orderItems->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            }

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

    public function approveOrder(Request $request, $orderId)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id'
            ]);

            $order = Order::with([
                'orderItems' => function ($query) {
                    $query->with([
                        'product' => function ($q) {
                            $q->with(['galleries', 'category', 'variants']);
                        }
                    ]);
                },
                'transaction',
                'user'
            ])->findOrFail($orderId);

            // Verify merchant ownership
            if ($order->merchant_id !== $request->merchant_id) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: Order does not belong to this merchant',
                    403
                );
            }

            // Verify order can be approved
            if ($order->order_status !== Order::STATUS_WAITING_APPROVAL) {
                return ResponseFormatter::error(
                    null,
                    'Order cannot be approved: Invalid status',
                    400
                );
            }

            DB::beginTransaction();
            try {
                // Update order status
                $order->order_status = Order::STATUS_PROCESSING;
                $order->merchant_approval = Order::MERCHANT_APPROVED;
                $order->save();

                // Send notification to user if needed
                if ($this->firebaseService) {
                    $this->firebaseService->sendNotification(
                        $order->user_id,
                        'Order Approved',
                        'Your order has been approved and is being processed'
                    );
                }

                DB::commit();

                return ResponseFormatter::success(
                    $order,
                    'Order approved successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to approve order: ' . $e->getMessage(),
                500
            );
        }
    }

    public function rejectOrder(Request $request, $orderId)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id'
            ]);

            $order = Order::with([
                'orderItems' => function ($query) {
                    $query->with([
                        'product' => function ($q) {
                            $q->with(['galleries', 'category', 'variants']);
                        }
                    ]);
                },
                'transaction',
                'user'
            ])->findOrFail($orderId);

            // Verify merchant ownership
            if ($order->merchant_id !== $request->merchant_id) {
                return ResponseFormatter::error(
                    null,
                    'Unauthorized: Order does not belong to this merchant',
                    403
                );
            }

            // Verify order can be rejected
            if ($order->order_status !== Order::STATUS_WAITING_APPROVAL) {
                return ResponseFormatter::error(
                    null,
                    'Order cannot be rejected: Invalid status',
                    400
                );
            }

            DB::beginTransaction();
            try {
                // Update order status
                $order->order_status = Order::STATUS_CANCELED;
                $order->merchant_approval = Order::MERCHANT_REJECTED;
                $order->save();

                // Check if all orders in transaction are canceled
                $transaction = $order->transaction;
                if ($transaction->allOrdersCanceled()) {
                    $transaction->status = Transaction::STATUS_CANCELED;
                    $transaction->save();
                }

                // Send notification to user
                if ($this->firebaseService) {
                    $this->firebaseService->sendNotification(
                        $order->user_id,
                        'Order Rejected',
                        'Your order has been rejected by the merchant'
                    );
                }

                DB::commit();

                return ResponseFormatter::success(
                    $order,
                    'Order rejected successfully'
                );
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to reject order: ' . $e->getMessage(),
                500
            );
        }
    }

    public function markAsReady(Request $request, $orderId)
    {
        try {
            $request->validate([
                'merchant_id' => 'required|exists:merchants,id'
            ]);

            $order = Order::with([
                'orderItems' => function ($query) {
                    $query->with([
                        'product' => function ($q) {
                            $q->with(['galleries', 'category', 'variants']);
                        }
                    ]);
                },
                'transaction',
                'user'
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

                // Send notification to user
                if ($this->firebaseService) {
                    $this->firebaseService->sendNotification(
                        $order->user_id,
                        'Order Ready for Pickup',
                        'Your order is ready for pickup'
                    );
                }

                DB::commit();

                return ResponseFormatter::success(
                    $order,
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
}

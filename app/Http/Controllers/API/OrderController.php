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
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $status = $request->input('status'); // Get status from request
            
            $query = Order::with([
                'orderItems' => function ($query) {
                    $query->with([
                        'product' => function ($q) {
                            $q->with(['galleries', 'category', 'variants']);
                        }
                    ]);
                },
                'transaction',
                'user'
            ])
                ->where('merchant_id', $merchantId)
                ->whereHas('transaction', function($q) {
                    $q->where('courier_approval', 'APPROVED');
                });

            // Filter by status if provided
            if ($status) {
                $query->where('order_status', $status);
            }

            // Exclude PENDING orders since they shouldn't be visible to merchant
            $query->where('order_status', '!=', Order::STATUS_PENDING);

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Get count for each status in a single query
            $statusCounts = Order::where('merchant_id', $merchantId)
                ->select('order_status', DB::raw('count(*) as total'))
                ->groupBy('order_status')
                ->pluck('total', 'order_status')
                ->toArray();

            // Ensure all statuses have a count, even if zero
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

            return ResponseFormatter::success([
                'orders' => $orders,
                'status_counts' => $statusCounts
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

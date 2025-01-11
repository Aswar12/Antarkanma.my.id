<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_location_id' => 'required|exists:user_locations,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:MANUAL,ONLINE',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);
            $userLocation = UserLocation::findOrFail($request->user_location_id);

            // Create Order
            $order = new Order();
            $order->user_id = $user->id;
            $order->order_status = 'PENDING';
            $order->save();

            $totalAmount = 0;

            // Create Order Items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = isset($item['variant_id']) ? $product->variants()->findOrFail($item['variant_id']) : null;
                
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $product->id;
                $orderItem->product_variant_id = $variant ? $variant->id : null;
                $orderItem->merchant_id = $product->merchant_id;
                $orderItem->quantity = $item['quantity'];
                
                // Calculate price including variant adjustment if any
                $basePrice = $product->price;
                if ($variant) {
                    $basePrice += $variant->price_adjustment;
                }
                $orderItem->price = $basePrice;
                $orderItem->save();

                $totalAmount += $basePrice * $item['quantity'];
            }

            // Update Order total amount
            $order->total_amount = $totalAmount;
            $order->save();

            // Create Transaction
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->user_id = $user->id;
            $transaction->user_location_id = $userLocation->id;
            $transaction->total_price = $totalAmount;
            $transaction->shipping_price = 0;
            $transaction->status = 'PENDING';
            $transaction->payment_method = $request->payment_method;
            $transaction->payment_status = 'PENDING';
            $transaction->save();

            DB::commit();

            return ResponseFormatter::success([
                'order' => $order->load('orderItems', 'transaction'),
            ], 'Order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Order creation failed: ' . $e->getMessage(), 500);
        }
    }

    public function list()
    {
        try {
            $orders = Order::with(['orderItems.product', 'transaction'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return ResponseFormatter::success($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        try {
            $order = Order::with(['orderItems.product', 'transaction'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return ResponseFormatter::success($order, 'Order retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Order not found', 404);
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);

            if ($order->order_status !== 'PENDING') {
                return ResponseFormatter::error(null, 'Only pending orders can be canceled', 400);
            }

            $order->order_status = 'CANCELED';
            $order->save();

            if ($order->transaction) {
                $order->transaction->status = 'CANCELED';
                $order->transaction->save();
            }

            DB::commit();
            return ResponseFormatter::success($order, 'Order canceled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to cancel order: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:PENDING,PROCESSING,COMPLETED,CANCELED'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            $order->order_status = $request->status;
            $order->save();

            if ($order->transaction) {
                $order->transaction->status = $request->status;
                $order->transaction->save();
            }

            DB::commit();
            return ResponseFormatter::success($order, 'Order status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to update order status: ' . $e->getMessage(), 500);
        }
    }

    public function getMerchantOrders(Request $request)
    {
        try {
            $merchantId = Auth::user()->merchant->id;
            $status = $request->input('status');

            $query = Order::whereHas('orderItems', function ($query) use ($merchantId) {
                $query->where('merchant_id', $merchantId);
            })->with(['orderItems.product', 'transaction', 'user']);

            if ($status) {
                $query->where('order_status', $status);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate(10);

            return ResponseFormatter::success($orders, 'Merchant orders retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve merchant orders: ' . $e->getMessage(), 500);
        }
    }

    public function getMerchantOrdersSummary()
    {
        try {
            $merchantId = Auth::user()->merchant->id;
            
            // Get base statistics
            $statistics = [
                'total_orders' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->count(),
                'pending_orders' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->where('order_status', 'PENDING')->count(),
                'processing_orders' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->where('order_status', 'PROCESSING')->count(),
                'completed_orders' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->where('order_status', 'COMPLETED')->count(),
                'canceled_orders' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->where('order_status', 'CANCELED')->count(),
                'total_revenue' => Order::whereHas('orderItems', function ($query) use ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                })->where('order_status', 'COMPLETED')
                  ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                  ->where('order_items.merchant_id', $merchantId)
                  ->sum(DB::raw('order_items.quantity * order_items.price'))
            ];

            // Get orders with items grouped by status
            $ordersByStatus = [
                'pending' => $this->getOrdersWithItems($merchantId, 'PENDING'),
                'processing' => $this->getOrdersWithItems($merchantId, 'PROCESSING'),
                'completed' => $this->getOrdersWithItems($merchantId, 'COMPLETED'),
                'canceled' => $this->getOrdersWithItems($merchantId, 'CANCELED')
            ];

            return ResponseFormatter::success([
                'statistics' => $statistics,
                'orders' => $ordersByStatus
            ], 'Merchant order summary retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve merchant order summary: ' . $e->getMessage(), 500);
        }
    }

    private function getOrdersWithItems($merchantId, $status)
    {
        return Order::whereHas('orderItems', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
        ->where('order_status', $status)
        ->with(['orderItems' => function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId)
                  ->with(['product' => function ($query) {
                      $query->select('id', 'name', 'price', 'description')
                           ->with(['galleries' => function($q) {
                               $q->select('id', 'product_id', 'url');
                           }])
                           ->with(['variants' => function($q) {
                               $q->select('id', 'product_id', 'name', 'value', 'price_adjustment');
                           }]);
                  }, 'variant']);
        }, 'user:id,name,email,phone_number'])
        ->select('id', 'user_id', 'order_status', 'total_amount', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->order_status,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
                'customer' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'phone' => $order->user->phone_number
                ],
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'description' => $item->product->description,
                            'galleries' => $item->product->galleries->map(function ($gallery) {
                                return [
                                    'id' => $gallery->id,
                                    'url' => $gallery->url
                                ];
                            }),
                            'variants' => $item->product->variants->map(function ($variant) {
                                return [
                                    'id' => $variant->id,
                                    'name' => $variant->name,
                                    'value' => $variant->value,
                                    'price_adjustment' => $variant->price_adjustment
                                ];
                            })
                        ],
                        'variant' => $item->variant ? [
                            'id' => $item->variant->id,
                            'name' => $item->variant->name,
                            'value' => $item->variant->value,
                            'price_adjustment' => $item->variant->price_adjustment
                        ] : null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price
                    ];
                })
            ];
        });
    }

    public function getOrderStatistics()
    {
        try {
            $userId = Auth::id();
            
            $statistics = [
                'total_orders' => Order::where('user_id', $userId)->count(),
                'pending_orders' => Order::where('user_id', $userId)->where('order_status', 'PENDING')->count(),
                'completed_orders' => Order::where('user_id', $userId)->where('order_status', 'COMPLETED')->count(),
                'canceled_orders' => Order::where('user_id', $userId)->where('order_status', 'CANCELED')->count(),
            ];

            return ResponseFormatter::success($statistics, 'Order statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve order statistics: ' . $e->getMessage(), 500);
        }
    }
}

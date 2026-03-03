<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\UserLocation;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ManualOrderController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Create manual order (Jastip)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string|max:500',
                'user_location_id' => 'required|exists:user_locations,id',
                'delivery_address' => 'required|string|max:500',
                'delivery_latitude' => 'required|numeric|between:-90,90',
                'delivery_longitude' => 'required|numeric|between:-180,180',
                'phone_number' => 'required|string|max:20',
                'customer_name' => 'required|string|max:255',
                'merchant_name' => 'required|string|max:255',
                'merchant_address' => 'required|string|max:500',
                'merchant_phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
                'payment_method' => 'nullable|in:MANUAL,ONLINE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validasi gagal',
                        'details' => $validator->errors()
                    ]
                ], 422);
            }

            $user = $request->user();
            $data = $validator->validated();

            DB::beginTransaction();

            try {
                // Calculate subtotal
                $subtotal = collect($data['items'])
                    ->sum(fn($item) => $item['price'] * $item['quantity']);
                
                // Calculate shipping cost (simplified - should use ShippingService in production)
                $shippingCost = $this->calculateShippingCost(
                    $data['delivery_latitude'],
                    $data['delivery_longitude']
                );
                
                // Platform fee for manual order (higher than regular)
                $platformFee = 2000; // Rp 2.000 for manual order
                
                $totalAmount = $subtotal + $shippingCost + $platformFee;

                // Create order with manual order flag
                $order = Order::create([
                    'transaction_id' => null, // Will be set after transaction created
                    'user_id' => $user->id,
                    'merchant_id' => null, // No specific merchant for manual orders
                    'total_amount' => $totalAmount,
                    'order_status' => Order::STATUS_WAITING_APPROVAL,
                    'merchant_approval' => Order::MERCHANT_PENDING,
                    'is_manual_order' => true,
                    'manual_merchant_name' => $data['merchant_name'],
                    'manual_merchant_address' => $data['merchant_address'],
                    'manual_merchant_phone' => $data['merchant_phone'] ?? null,
                ]);

                // Create order items
                foreach ($data['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null, // No specific product for manual orders
                        'merchant_id' => null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'customer_note' => $item['notes'] ?? null,
                        'item_name' => $item['name'],
                    ]);
                }

                // Get user location
                $userLocation = UserLocation::find($data['user_location_id']);
                
                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'user_location_id' => $data['user_location_id'],
                    'total_price' => $subtotal,
                    'shipping_price' => $shippingCost,
                    'status' => Transaction::STATUS_PENDING,
                    'payment_method' => $data['payment_method'] ?? Transaction::PAYMENT_MANUAL,
                    'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                    'courier_approval' => Transaction::COURIER_PENDING,
                    'courier_status' => Transaction::COURIER_STATUS_IDLE,
                    'note' => $data['notes'] ?? null,
                ]);

                // Link order to transaction
                $order->update(['transaction_id' => $transaction->id]);

                // Send FCM notification to admin (manual orders need admin approval)
                $this->notifyAdmin($order, $user, $userLocation);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order manual berhasil dibuat. Menunggu konfirmasi admin.',
                    'data' => [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                        'total_amount' => $totalAmount,
                        'subtotal' => $subtotal,
                        'shipping_cost' => $shippingCost,
                        'platform_fee' => $platformFee,
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('ManualOrderController::store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Gagal membuat order manual: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Calculate shipping cost for manual order
     * 
     * @param float $latitude
     * @param float $longitude
     * @return float
     */
    private function calculateShippingCost(float $latitude, float $longitude): float
    {
        // Simplified calculation - should use ShippingService in production
        // Base fare: Rp 5.000 for first 3km
        // Rp 2.000 per km after that
        
        // For now, return fixed rate as placeholder
        // In production, calculate based on actual distance from merchant
        return 5000;
    }

    /**
     * Send notification to admin for manual order approval
     * 
     * @param Order $order
     * @param mixed $user
     * @param UserLocation|null $location
     * @return void
     */
    private function notifyAdmin(Order $order, $user, ?UserLocation $location): void
    {
        try {
            // Get admin users
            $adminUsers = \App\Models\User::where('role', 'ADMIN')->get();
            
            foreach ($adminUsers as $admin) {
                $this->firebaseService->sendToUser(
                    $admin->fcmTokens()->where('is_active', true)->pluck('token')->toArray(),
                    [
                        'type' => 'MANUAL_ORDER',
                        'order_id' => $order->id,
                        'customer_name' => $user->name,
                        'merchant_name' => $order->manual_merchant_name,
                    ],
                    'Order Manual Baru',
                    "{$user->name} membuat order manual dari {$order->manual_merchant_name}"
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the order
            Log::error('Failed to send admin notification for manual order: ' . $e->getMessage());
        }
    }
}

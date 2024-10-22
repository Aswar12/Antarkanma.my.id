<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Order;
use App\Models\Transaction;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function assignCourier(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'courier_id' => 'required|exists:couriers,id',
            'estimated_delivery_time' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::findOrFail($request->transaction_id);
            $courier = Courier::findOrFail($request->courier_id);

            // Create Delivery
            $delivery = new Delivery();
            $delivery->transaction_id = $transaction->id;
            $delivery->courier_id = $courier->id;
            $delivery->delivery_status = 'PENDING';
            $delivery->estimated_delivery_time = $request->estimated_delivery_time;
            $delivery->save();

            // Create Delivery Items
            $order = Order::findOrFail($transaction->order_id);
            foreach ($order->items as $orderItem) {
                $deliveryItem = new DeliveryItem();
                $deliveryItem->delivery_id = $delivery->id;
                $deliveryItem->order_item_id = $orderItem->id;
                $deliveryItem->pickup_status = 'PENDING';
                $deliveryItem->save();
            }

            // Update Transaction status
            $transaction->status = 'PROCESSING';
            $transaction->save();

            // Update Order status
            $order->order_status = 'PROCESSING';
            $order->save();

            DB::commit();

            return ResponseFormatter::success([
                'delivery' => $delivery->load('items', 'courier'),
            ], 'Courier assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Courier assignment failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateDeliveryStatus(Request $request, $deliveryId)
    {
        $request->validate([
            'status' => 'required|in:PENDING,IN_PROGRESS,DELIVERED,CANCELED',
        ]);

        DB::beginTransaction();

        try {
            $delivery = Delivery::findOrFail($deliveryId);
            $delivery->delivery_status = $request->status;

            if ($request->status == 'DELIVERED') {
                $delivery->actual_delivery_time = now();
            }

            $delivery->save();

            // Update Transaction and Order status if delivery is completed or canceled
            if (in_array($request->status, ['DELIVERED', 'CANCELED'])) {
                $transaction = $delivery->transaction;
                $order = $transaction->order;

                $transaction->status = $request->status == 'DELIVERED' ? 'COMPLETED' : 'CANCELED';
                $transaction->save();

                $order->order_status = $request->status == 'DELIVERED' ? 'COMPLETED' : 'CANCELED';
                $order->save();
            }

            DB::commit();

            return ResponseFormatter::success([
                'delivery' => $delivery->load('transaction.order'),
            ], 'Delivery status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Delivery status update failed: ' . $e->getMessage(), 500);
        }
    }

    public function updatePickupStatus(Request $request, $deliveryItemId)
    {
        $request->validate([
            'status' => 'required|in:PENDING,PICKED_UP',
            'pickup_time' => 'required_if:status,PICKED_UP|date',
        ]);

        DB::beginTransaction();

        try {
            $deliveryItem = DeliveryItem::findOrFail($deliveryItemId);
            $deliveryItem->pickup_status = $request->status;

            if ($request->status == 'PICKED_UP') {
                $deliveryItem->pickup_time = $request->pickup_time;
            }

            $deliveryItem->save();

            // Check if all items are picked up
            $allItemsPickedUp = $deliveryItem->delivery->items()->where('pickup_status', '!=', 'PICKED_UP')->count() == 0;

            if ($allItemsPickedUp) {
                $deliveryItem->delivery->delivery_status = 'IN_PROGRESS';
                $deliveryItem->delivery->save();
            }

            DB::commit();

            return ResponseFormatter::success([
                'delivery_item' => $deliveryItem->load('delivery'),
            ], 'Pickup status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Pickup status update failed: ' . $e->getMessage(), 500);
        }
    }

    public function getCourierDeliveries(Request $request, $courierId)
    {
        $request->validate([
            'status' => 'nullable|in:PENDING,IN_PROGRESS,DELIVERED,CANCELED',
            'date' => 'nullable|date',
        ]);

        $deliveries = Delivery::where('courier_id', $courierId)
            ->when($request->status, function ($query) use ($request) {
                return $query->where('delivery_status', $request->status);
            })
            ->when($request->date, function ($query) use ($request) {
                return $query->whereDate('created_at', $request->date);
            })
            ->with(['transaction.order', 'items'])
            ->paginate(10);

        return ResponseFormatter::success(
            $deliveries,
            'Courier deliveries retrieved successfully'
        );
    }
}

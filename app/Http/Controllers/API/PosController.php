<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    /**
     * Get products for POS grid (optimized: name, price, variants, 1 image)
     */
    public function getProducts(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $query = Product::where('merchant_id', $merchantId)
                ->select('id', 'name', 'price', 'merchant_id', 'status')
                ->with([
                    'galleries' => function ($q) {
                        $q->select('id', 'product_id', 'url')->limit(1);
                    },
                    'variants' => function ($q) {
                        $q->select('id', 'product_id', 'name', 'price');
                    },
                    'category' => function ($q) {
                        $q->select('id', 'name');
                    },
                ]);

            // Optional category filter
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Optional search
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $products = $query->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();

            return ResponseFormatter::success($products, 'POS products retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new POS transaction
     */
    public function createTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:DINE_IN,TAKEAWAY,DELIVERY',
            'payment_method' => 'required|in:CASH,QRIS,TRANSFER',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_address' => 'required_if:order_type,DELIVERY|nullable|string',
            'table_number' => 'nullable|string|max:20',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors()->first(), 422);
        }

        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            DB::beginTransaction();

            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $subtotal += ($item['price'] * $item['quantity']) - $itemDiscount;
            }

            $discount = $request->input('discount', 0);
            $tax = $request->input('tax', 0);
            $total = $subtotal - $discount + $tax;
            $amountPaid = $request->input('amount_paid', $total);
            $changeAmount = max(0, $amountPaid - $total);

            // Create transaction
            $transaction = PosTransaction::create([
                'merchant_id' => $merchantId,
                'transaction_code' => PosTransaction::generateTransactionCode($merchantId),
                'order_type' => $request->order_type,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'notes' => $request->notes,
                'status' => 'COMPLETED',
                'table_number' => $request->table_number,
                'created_by' => $request->user()->id,
            ]);

            // Create items
            foreach ($request->items as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                PosTransactionItem::create([
                    'pos_transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $itemDiscount,
                    'subtotal' => ($item['price'] * $item['quantity']) - $itemDiscount,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $transaction->load('items', 'createdByUser');

            return ResponseFormatter::success($transaction, 'POS transaction created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error('Failed to create transaction: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get POS transactions list with filters
     */
    public function getTransactions(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $query = PosTransaction::where('merchant_id', $merchantId)
                ->with('items');

            // Filter by order type
            if ($request->has('order_type')) {
                $query->byType($request->order_type);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            $query->dateRange($request->input('from'), $request->input('to'));

            // Search by transaction code or customer name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_code', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%");
                });
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            return ResponseFormatter::success($transactions, 'POS transactions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve transactions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single POS transaction detail
     */
    public function getTransaction(Request $request, $id)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $transaction = PosTransaction::where('merchant_id', $merchantId)
                ->with(['items.product', 'items.productVariant', 'createdByUser'])
                ->find($id);

            if (!$transaction) {
                return ResponseFormatter::error('Transaction not found', 404);
            }

            return ResponseFormatter::success($transaction, 'Transaction detail retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve transaction: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Void a POS transaction
     */
    public function voidTransaction(Request $request, $id)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $transaction = PosTransaction::where('merchant_id', $merchantId)->find($id);

            if (!$transaction) {
                return ResponseFormatter::error('Transaction not found', 404);
            }

            if (!$transaction->canBeVoided()) {
                return ResponseFormatter::error(
                    'Transaction cannot be voided. It may already be voided or has an active delivery.',
                    422
                );
            }

            $transaction->update(['status' => 'VOIDED']);

            return ResponseFormatter::success($transaction, 'Transaction voided successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to void transaction: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get daily summary for POS
     */
    public function getDailySummary(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $date = $request->input('date', now()->toDateString());

            $baseQuery = PosTransaction::where('merchant_id', $merchantId)
                ->whereDate('created_at', $date)
                ->where('status', 'COMPLETED');

            $summary = [
                'date' => $date,
                'total_transactions' => (clone $baseQuery)->count(),
                'total_revenue' => (clone $baseQuery)->sum('total'),
                'by_type' => [
                    'dine_in' => [
                        'count' => (clone $baseQuery)->where('order_type', 'DINE_IN')->count(),
                        'total' => (clone $baseQuery)->where('order_type', 'DINE_IN')->sum('total'),
                    ],
                    'takeaway' => [
                        'count' => (clone $baseQuery)->where('order_type', 'TAKEAWAY')->count(),
                        'total' => (clone $baseQuery)->where('order_type', 'TAKEAWAY')->sum('total'),
                    ],
                    'delivery' => [
                        'count' => (clone $baseQuery)->where('order_type', 'DELIVERY')->count(),
                        'total' => (clone $baseQuery)->where('order_type', 'DELIVERY')->sum('total'),
                    ],
                ],
                'by_payment' => [
                    'cash' => (clone $baseQuery)->where('payment_method', 'CASH')->sum('total'),
                    'qris' => (clone $baseQuery)->where('payment_method', 'QRIS')->sum('total'),
                    'transfer' => (clone $baseQuery)->where('payment_method', 'TRANSFER')->sum('total'),
                ],
            ];

            return ResponseFormatter::success($summary, 'Daily summary retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve daily summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merchant ID for authenticated user
     */
    private function getMerchantId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) return null;

        $merchant = Merchant::where('owner_id', $user->id)->first();
        return $merchant?->id;
    }
}

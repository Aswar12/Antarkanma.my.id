<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\MerchantExpense;
use App\Models\Merchant;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MerchantFinanceController extends Controller
{
    /**
     * Get financial overview: income (online + POS), expenses, net profit
     */
    public function getOverview(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $from = $request->input('from');
            $to = $request->input('to');

            // POS income
            $posQuery = PosTransaction::where('merchant_id', $merchantId)
                ->where('status', 'COMPLETED')
                ->dateRange($from, $to);
            $posIncome = (clone $posQuery)->sum('total');
            $posCount = (clone $posQuery)->count();

            // Online order income (from existing orders table)
            $onlineQuery = DB::table('orders')
                ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
                ->where('transactions.merchant_id', $merchantId)
                ->where('orders.order_status', 'COMPLETED');
            if ($from) $onlineQuery->whereDate('orders.created_at', '>=', $from);
            if ($to) $onlineQuery->whereDate('orders.created_at', '<=', $to);
            $onlineIncome = (clone $onlineQuery)->sum('orders.total_amount');
            $onlineCount = (clone $onlineQuery)->count();

            // Expenses
            $expenseQuery = MerchantExpense::where('merchant_id', $merchantId)
                ->dateRange($from, $to);
            $totalExpenses = (clone $expenseQuery)->sum('amount');

            $totalIncome = $posIncome + $onlineIncome;
            $netProfit = $totalIncome - $totalExpenses;

            $data = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'income_breakdown' => [
                    'pos' => [
                        'amount' => $posIncome,
                        'count' => $posCount,
                    ],
                    'online' => [
                        'amount' => $onlineIncome,
                        'count' => $onlineCount,
                    ],
                ],
                'expense_categories' => $this->getExpensesByCategory($merchantId, $from, $to),
            ];

            return ResponseFormatter::success($data, 'Financial overview retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve financial overview: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get income breakdown by period
     */
    public function getIncomeBreakdown(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $period = $request->input('period', 'daily'); // daily, weekly, monthly
            $from = $request->input('from', now()->subDays(30)->toDateString());
            $to = $request->input('to', now()->toDateString());

            $dateFormat = match ($period) {
                'weekly' => '%Y-%u',
                'monthly' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            // POS income by period
            $posData = PosTransaction::where('merchant_id', $merchantId)
                ->where('status', 'COMPLETED')
                ->dateRange($from, $to)
                ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Online income by period
            $onlineData = DB::table('orders')
                ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
                ->where('transactions.merchant_id', $merchantId)
                ->where('orders.order_status', 'COMPLETED')
                ->whereDate('orders.created_at', '>=', $from)
                ->whereDate('orders.created_at', '<=', $to)
                ->select(DB::raw("DATE_FORMAT(orders.created_at, '{$dateFormat}') as period"), DB::raw('SUM(orders.total_amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return ResponseFormatter::success([
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'pos' => $posData,
                'online' => $onlineData,
            ], 'Income breakdown retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve income breakdown: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merchant expenses list
     */
    public function getExpenses(Request $request)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $query = MerchantExpense::where('merchant_id', $merchantId);

            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            $query->dateRange($request->input('from'), $request->input('to'));

            $expenses = $query->orderBy('expense_date', 'desc')
                ->paginate($request->input('per_page', 20));

            return ResponseFormatter::success($expenses, 'Expenses retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to retrieve expenses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new expense
     */
    public function createExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:BAHAN_BAKU,OPERASIONAL,GAJI,SEWA,UTILITAS,LAINNYA',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'receipt_image' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors()->first(), 422);
        }

        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $expense = MerchantExpense::create([
                'merchant_id' => $merchantId,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'expense_date' => $request->expense_date,
                'receipt_image' => $request->receipt_image,
            ]);

            return ResponseFormatter::success($expense, 'Expense created successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to create expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an expense
     */
    public function updateExpense(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|in:BAHAN_BAKU,OPERASIONAL,GAJI,SEWA,UTILITAS,LAINNYA',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'expense_date' => 'nullable|date',
            'receipt_image' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors()->first(), 422);
        }

        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $expense = MerchantExpense::where('merchant_id', $merchantId)->find($id);
            if (!$expense) {
                return ResponseFormatter::error('Expense not found', 404);
            }

            $expense->update($request->only([
                'category', 'amount', 'description', 'expense_date', 'receipt_image'
            ]));

            return ResponseFormatter::success($expense, 'Expense updated successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to update expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an expense
     */
    public function deleteExpense(Request $request, $id)
    {
        try {
            $merchantId = $this->getMerchantId($request);
            if (!$merchantId) {
                return ResponseFormatter::error('Merchant not found', 404);
            }

            $expense = MerchantExpense::where('merchant_id', $merchantId)->find($id);
            if (!$expense) {
                return ResponseFormatter::error('Expense not found', 404);
            }

            $expense->delete();

            return ResponseFormatter::success(null, 'Expense deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error('Failed to delete expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get expenses grouped by category
     */
    private function getExpensesByCategory(int $merchantId, ?string $from, ?string $to): array
    {
        $query = MerchantExpense::where('merchant_id', $merchantId)
            ->dateRange($from, $to)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        return $query->toArray();
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

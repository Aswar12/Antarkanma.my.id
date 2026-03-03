<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get sales data grouped by period
     */
    public function getSalesData(string $period = 'daily', ?string $from = null, ?string $to = null, ?int $merchantId = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $groupFormat = match ($period) {
            'weekly' => '%x-W%v',     // 2026-W09
            'monthly' => '%Y-%m',     // 2026-03
            'yearly' => '%Y',         // 2026
            default => '%Y-%m-%d',    // 2026-03-03
        };

        $query = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to]);

        if ($merchantId) {
            $query->where('orders.merchant_id', $merchantId);
        }

        $salesData = $query
            ->select(
                DB::raw("DATE_FORMAT(transactions.created_at, '$groupFormat') as period"),
                DB::raw('COUNT(DISTINCT transactions.id) as total_transactions'),
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_sales'),
                DB::raw('SUM(transactions.shipping_price) as total_shipping'),
                DB::raw('SUM(orders.total_amount) + SUM(transactions.shipping_price) as total_revenue')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();

        // Summary
        $summary = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to]);

        if ($merchantId) {
            $summary->where('orders.merchant_id', $merchantId);
        }

        $summaryData = $summary
            ->select(
                DB::raw('COUNT(DISTINCT transactions.id) as total_transactions'),
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_sales'),
                DB::raw('COALESCE(SUM(transactions.shipping_price), 0) as total_shipping'),
                DB::raw('COALESCE(SUM(orders.total_amount) + SUM(transactions.shipping_price), 0) as total_revenue')
            )
            ->first();

        return [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $summaryData,
            'data' => $salesData,
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10, ?string $from = null, ?string $to = null, ?int $merchantId = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to]);

        if ($merchantId) {
            $query->where('order_items.merchant_id', $merchantId);
        }

        return $query
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top merchants by revenue
     */
    public function getTopMerchants(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        return DB::table('orders')
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('merchants', 'orders.merchant_id', '=', 'merchants.id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select(
                'merchants.id',
                'merchants.name as merchant_name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_revenue'),
                DB::raw('AVG(orders.total_amount) as avg_order_value')
            )
            ->groupBy('merchants.id', 'merchants.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top couriers by delivery count
     */
    public function getTopCouriers(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        return DB::table('transactions')
            ->join('users', 'transactions.courier_id', '=', 'users.id')
            ->where('transactions.status', 'SUCCESS')
            ->whereNotNull('transactions.courier_id')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select(
                'users.id',
                'users.name as courier_name',
                DB::raw('COUNT(transactions.id) as total_deliveries'),
                DB::raw('SUM(transactions.shipping_price) as total_earnings'),
                DB::raw('AVG(transactions.rating) as avg_rating')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_deliveries')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get peak hours analysis
     */
    public function getPeakHours(?string $from = null, ?string $to = null, ?int $merchantId = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $query = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to]);

        if ($merchantId) {
            $query->where('orders.merchant_id', $merchantId);
        }

        return $query
            ->select(
                DB::raw('HOUR(transactions.created_at) as hour'),
                DB::raw('COUNT(DISTINCT transactions.id) as order_count'),
                DB::raw('SUM(orders.total_amount) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    /**
     * Get revenue breakdown
     */
    public function getRevenueBreakdown(?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $data = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select(
                DB::raw('COALESCE(SUM(orders.total_amount), 0) as product_revenue'),
                DB::raw('COALESCE(SUM(transactions.shipping_price), 0) as shipping_revenue'),
                DB::raw('COALESCE(SUM(orders.total_amount) + SUM(transactions.shipping_price), 0) as total_revenue')
            )
            ->first();

        // Revenue by category
        $byCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select(
                'product_categories.name as category',
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->groupBy('product_categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();

        return [
            'overview' => $data,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get customer behavior analytics
     */
    public function getCustomerBehavior(?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        // Total unique customers
        $totalCustomers = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->distinct('user_id')
            ->count('user_id');

        // New customers (first order in period)
        $newCustomers = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('user_id', function ($q) use ($from) {
                $q->select('user_id')
                    ->from('transactions')
                    ->where('status', 'SUCCESS')
                    ->groupBy('user_id')
                    ->havingRaw('MIN(created_at) >= ?', [$from]);
            })
            ->distinct('user_id')
            ->count('user_id');

        $returningCustomers = $totalCustomers - $newCustomers;

        // Average order value
        $avgOrderValue = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->avg('total_price') ?? 0;

        // Repeat order rate
        $customersWithMultipleOrders = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $repeatRate = $totalCustomers > 0
            ? round(($customersWithMultipleOrders / $totalCustomers) * 100, 1)
            : 0;

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'repeat_order_rate' => $repeatRate,
            'avg_order_value' => round($avgOrderValue, 0),
        ];
    }

    /**
     * Get courier earnings data
     */
    public function getCourierEarnings(int $courierId, string $period = 'daily', ?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $groupFormat = match ($period) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $earningsData = DB::table('transactions')
            ->where('courier_id', $courierId)
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
                DB::raw('COUNT(*) as deliveries'),
                DB::raw('SUM(shipping_price) as earnings')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();

        $summary = DB::table('transactions')
            ->where('courier_id', $courierId)
            ->where('status', 'SUCCESS')
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('COUNT(*) as total_deliveries'),
                DB::raw('COALESCE(SUM(shipping_price), 0) as total_earnings'),
                DB::raw('AVG(rating) as avg_rating')
            )
            ->first();

        return [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $summary,
            'data' => $earningsData,
        ];
    }
}

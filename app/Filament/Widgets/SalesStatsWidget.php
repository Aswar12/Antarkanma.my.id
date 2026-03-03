<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'full';

    // Only show on Analytics page
    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics');
    }

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);

        // Today
        $todaySales = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereDate('transactions.created_at', Carbon::today())
            ->sum('orders.total_amount');

        // This week
        $weekSales = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('orders.total_amount');

        // This month
        $monthSales = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereMonth('transactions.created_at', Carbon::now()->month)
            ->whereYear('transactions.created_at', Carbon::now()->year)
            ->sum('orders.total_amount');

        // Last month for growth
        $lastMonthSales = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereMonth('transactions.created_at', Carbon::now()->subMonth()->month)
            ->whereYear('transactions.created_at', Carbon::now()->subMonth()->year)
            ->sum('orders.total_amount');

        $growth = $lastMonthSales > 0
            ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : ($monthSales > 0 ? 100 : 0);

        // Total orders today
        $todayOrders = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Customer behavior
        $behavior = $analytics->getCustomerBehavior(
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->toDateString()
        );

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                ->description($todayOrders . ' transaksi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Penjualan Minggu Ini', 'Rp ' . number_format($weekSales, 0, ',', '.'))
                ->description('Minggu berjalan')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Penjualan Bulan Ini', 'Rp ' . number_format($monthSales, 0, ',', '.'))
                ->description($growth >= 0 ? "+{$growth}% dari bulan lalu" : "{$growth}% dari bulan lalu")
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger'),

            Stat::make('Customer Aktif', $behavior['total_customers'])
                ->description($behavior['new_customers'] . ' customer baru, ' . $behavior['repeat_order_rate'] . '% repeat')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}

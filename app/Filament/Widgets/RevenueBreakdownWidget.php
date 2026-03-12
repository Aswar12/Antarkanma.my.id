<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueBreakdownWidget extends ChartWidget
{
    protected static ?int $sort = 16;
    protected static ?string $heading = 'Revenue Breakdown';
    protected static ?string $description = 'Distribusi pendapatan per kategori';
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics') || 
               str_contains(request()->path(), 'analytics');
    }

    protected function getData(): array
    {
        $byCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('transactions.status', 'SUCCESS')
            ->where('transactions.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'product_categories.name as category',
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->groupBy('product_categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        // Add shipping revenue
        $shippingRevenue = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('shipping_price');

        $labels = $byCategory->pluck('category')->toArray();
        $data = $byCategory->pluck('revenue')->map(fn ($v) => (float) $v)->toArray();

        if ($shippingRevenue > 0) {
            $labels[] = 'Ongkir';
            $data[] = (float) $shippingRevenue;
        }

        $colors = [
            'rgba(59, 130, 246, 0.7)',
            'rgba(16, 185, 129, 0.7)',
            'rgba(245, 158, 11, 0.7)',
            'rgba(239, 68, 68, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(107, 114, 128, 0.7)',
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

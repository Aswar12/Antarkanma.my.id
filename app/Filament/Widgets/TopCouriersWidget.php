<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopCouriersWidget extends ChartWidget
{
    protected static ?int $sort = 14;
    protected static ?string $heading = 'Top Kurir';
    protected static ?string $description = 'Berdasarkan jumlah pengiriman (30 hari)';
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics');
    }

    protected function getData(): array
    {
        $couriers = DB::table('transactions')
            ->join('users', 'transactions.courier_id', '=', 'users.id')
            ->where('transactions.status', 'SUCCESS')
            ->whereNotNull('transactions.courier_id')
            ->where('transactions.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'users.name',
                DB::raw('COUNT(*) as deliveries'),
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('deliveries')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pengiriman',
                    'data' => $couriers->pluck('deliveries')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                    ],
                ],
            ],
            'labels' => $couriers->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ];
    }
}

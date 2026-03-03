<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 11;
    protected static ?string $heading = 'Tren Penjualan';
    protected static ?string $description = '30 hari terakhir';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'daily';

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics');
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
    }

    protected function getData(): array
    {
        $period = $this->filter ?? 'daily';
        $days = match ($period) {
            'weekly' => 90,
            'monthly' => 365,
            default => 30,
        };

        $from = Carbon::now()->subDays($days);
        $to = Carbon::now()->endOfDay();

        $groupFormat = match ($period) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $salesData = DB::table('transactions')
            ->join('orders', 'transactions.id', '=', 'orders.transaction_id')
            ->where('transactions.status', 'SUCCESS')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(transactions.created_at, '$groupFormat') as period"),
                DB::raw('SUM(orders.total_amount) as revenue'),
                DB::raw('COUNT(DISTINCT transactions.id) as transactions')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $labels = $salesData->pluck('period')->toArray();
        $revenue = $salesData->pluck('revenue')->map(fn ($v) => (float) $v)->toArray();
        $transactions = $salesData->pluck('transactions')->map(fn ($v) => (int) $v)->toArray();

        // Format labels
        $formattedLabels = array_map(function ($label) use ($period) {
            return match ($period) {
                'monthly' => Carbon::createFromFormat('Y-m', $label)->format('M Y'),
                default => $label,
            };
        }, $labels);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $revenue,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Transaksi',
                    'data' => $transactions,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'type' => 'bar',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $formattedLabels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true, 'position' => 'left', 'title' => ['display' => true, 'text' => 'Revenue (Rp)']],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Transaksi']],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

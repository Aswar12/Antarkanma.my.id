<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeakHoursWidget extends ChartWidget
{
    protected static ?int $sort = 15;
    protected static ?string $heading = 'Jam Sibuk';
    protected static ?string $description = 'Distribusi pesanan per jam (30 hari)';
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics') || 
               str_contains(request()->path(), 'analytics');
    }

    protected function getData(): array
    {
        $peakHours = DB::table('transactions')
            ->where('status', 'SUCCESS')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $labels = [];
        $data = [];
        $colors = [];

        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $count = $peakHours->get($h)?->order_count ?? 0;
            $data[] = $count;

            // Color intensity based on order count
            $max = $peakHours->max('order_count') ?: 1;
            $intensity = $count / $max;
            $colors[] = "rgba(245, 158, 11, " . (0.3 + $intensity * 0.7) . ")";
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pesanan',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ];
    }
}

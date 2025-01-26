<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrderStatusPieChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Order Status Distribution';
    protected static ?string $description = 'Distribution of orders by status';

    protected function getData(): array
    {
        $data = Order::select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->order_status => $item->total])
            ->toArray();

        return [
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // green for COMPLETED
                        'rgb(234, 179, 8)',  // yellow for PROCESSING
                        'rgb(239, 68, 68)',  // red for CANCELED
                        'rgb(156, 163, 175)', // gray for PENDING
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

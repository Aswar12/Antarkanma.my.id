<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Orders Overview';
    protected static ?string $description = 'Last 7 days order trends';
    
    protected function getData(): array
    {
        $data = $this->getOrdersPerDay();
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data['orders'],
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
    
    protected function getOrdersPerDay(): array
    {
        $orders = Order::where('created_at', '>=', now()->subDays(7))
            ->get()
            ->groupBy(function ($order) {
                return $order->created_at->format('Y-m-d');
            });
            
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[] = $orders->get($date)?->count() ?? 0;
        }
        
        return [
            'orders' => $data,
            'labels' => $labels,
        ];
    }
}

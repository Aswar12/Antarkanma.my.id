<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\PopularProducts;
use App\Filament\Widgets\OrderStatusPieChart;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            OrdersChart::class,
            OrderStatusPieChart::class,
            LatestOrders::class,
            PopularProducts::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            OrdersChart::class,
            OrderStatusPieChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestOrders::class,
            PopularProducts::class,
        ];
    }
}

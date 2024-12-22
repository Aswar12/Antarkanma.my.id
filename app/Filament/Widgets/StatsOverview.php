<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Merchant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $totalRevenue = Order::where('order_status', 'COMPLETED')->sum('total_amount');
        
        return [
            Stat::make('Total Orders', Order::count())
                ->description('Total orders in the system')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),
                
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('From completed orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([4, 8, 3, 7, 5, 6, 8, 5])
                ->color('warning'),
                
            Stat::make('Active Products', Product::where('status', 'ACTIVE')->count())
                ->description('Products available for sale')
                ->descriptionIcon('heroicon-m-cube')
                ->chart([3, 5, 7, 4, 6, 3, 4, 7])
                ->color('primary'),
                
            Stat::make('Total Merchants', Merchant::count())
                ->description('Registered merchants')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart([5, 4, 6, 5, 7, 8, 6, 5])
                ->color('danger'),
        ];
    }
}

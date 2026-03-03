<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Filament\Widgets\SalesStatsWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\TopMerchantsWidget;
use App\Filament\Widgets\TopCouriersWidget;
use App\Filament\Widgets\PeakHoursWidget;
use App\Filament\Widgets\RevenueBreakdownWidget;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $title = 'Analytics Dashboard';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.analytics';

    protected function getHeaderActions(): array
    {
        $baseUrl = url('/api');

        return [
            Action::make('export_sales_csv')
                ->label('Export Sales CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url("{$baseUrl}/export/sales/csv")
                ->openUrlInNewTab(),

            Action::make('export_products_csv')
                ->label('Export Products CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url("{$baseUrl}/export/products/csv")
                ->openUrlInNewTab(),

            Action::make('export_report_pdf')
                ->label('Laporan PDF')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url("{$baseUrl}/export/sales/pdf")
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SalesChartWidget::class,
            TopProductsWidget::class,
            TopMerchantsWidget::class,
            TopCouriersWidget::class,
            RevenueBreakdownWidget::class,
            PeakHoursWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}

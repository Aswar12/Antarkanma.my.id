<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopMerchantsWidget extends BaseWidget
{
    protected static ?int $sort = 13;
    protected static ?string $heading = 'Merchant Terbaik';
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics') || 
               str_contains(request()->path(), 'analytics');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Merchant::query()
                    ->withCount(['orders as completed_orders' => function ($q) {
                        $q->whereHas('transaction', fn ($t) => $t->where('status', 'SUCCESS'));
                    }])
                    ->withSum(['orders as total_revenue' => function ($q) {
                        $q->whereHas('transaction', fn ($t) => $t->where('status', 'SUCCESS'));
                    }], 'total_amount')
                    ->orderByDesc('completed_orders')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Merchant')
                    ->searchable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('completed_orders')
                    ->label('Orders')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('completed_orders', 'desc');
    }
}

<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 12;
    protected static ?string $heading = 'Produk Terlaris';
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.analytics') || 
               str_contains(request()->path(), 'analytics');
    }

    public function table(Table $table): Table
    {
        $analytics = app(AnalyticsService::class);
        $products = $analytics->getTopProducts(10);

        // Convert to collection for Filament table
        $collection = collect($products)->map(function ($item, $index) {
            return (object) array_merge((array) $item, ['rank' => $index + 1]);
        });

        return $table
            ->query(
                \App\Models\Product::query()
                    ->whereIn('id', $collection->pluck('id'))
                    ->withCount(['orderItems as total_sold' => function ($q) {
                        $q->whereHas('order.transaction', fn ($t) => $t->where('status', 'SUCCESS'));
                    }])
                    ->orderByDesc('total_sold')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Terjual')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('total_sold', 'desc');
    }
}

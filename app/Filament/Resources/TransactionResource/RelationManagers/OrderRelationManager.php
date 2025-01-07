<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderRelationManager extends RelationManager
{
    protected static string $relationship = 'order';

    protected static ?string $title = 'Order Details';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['orderItems.product', 'orderItems.product.merchant']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('merchant_summary')
                    ->label('Merchants')
                    ->getStateUsing(function ($record) {
                        if (!$record->orderItems) return '';
                        
                        $merchantGroups = $record->orderItems->groupBy('merchant_id');
                        return $merchantGroups->map(function ($items) {
                            $firstItem = $items->first();
                            if (!$firstItem || !$firstItem->product || !$firstItem->product->merchant) {
                                return 'Unknown Merchant';
                            }
                            
                            $merchant = $firstItem->product->merchant;
                            $itemCount = $items->count();
                            $total = $items->sum(fn($item) => $item->price * $item->quantity);
                            return "{$merchant->name} ({$itemCount} items) - Rp " . number_format($total, 0, ',', '.');
                        })->join(', ');
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}

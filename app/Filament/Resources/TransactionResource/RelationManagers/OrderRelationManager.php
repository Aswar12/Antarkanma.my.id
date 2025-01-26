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
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['orderItems.product.merchant', 'user']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('merchant.name')
                    ->label('Merchant')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Items')
                    ->getStateUsing(function ($record) {
                        if (!$record || !$record->orderItems || $record->orderItems->isEmpty()) {
                            return 'No items';
                        }
                        
                        return $record->orderItems->map(function ($item) {
                            return "{$item->product->name} (x{$item->quantity})";
                        })->join(', ');
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('idr')
                    ->getStateUsing(fn ($record) => $record->total_amount ?? 0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'pending' => 'warning',
                        'waiting_approval' => 'info',
                        'processing' => 'info',
                        'ready_for_pickup' => 'success',
                        'picked_up' => 'success',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('merchant_approval')
                    ->label('Merchant Approval')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
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

<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'order.orderItems';

    protected static ?string $title = 'Order Items';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'product.merchant'])
            ->when(
                !is_null(request()->route('record')),
                fn (Builder $query) => $query->whereHas(
                    'order',
                    fn (Builder $query) => $query->where('id', request()->route('record'))
                )
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.merchant.name')
                    ->label('Merchant')
                    ->getStateUsing(fn ($record) => $record->product?->merchant?->name ?? 'Unknown Merchant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->getStateUsing(fn ($record) => $record->product?->name ?? 'Unknown Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price/Item')
                    ->money('idr')
                    ->getStateUsing(fn ($record) => $record->price ?? 0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('idr')
                    ->getStateUsing(fn ($record) => ($record->price ?? 0) * $record->quantity)
                    ->sortable(),
            ])
            ->defaultSort('product.merchant.name')
            ->grouping([
                'merchant' => Tables\Grouping\Group::make('product.merchant.name')
                    ->getStateUsing(fn ($record) => $record->product?->merchant?->name ?? 'Unknown Merchant')
                    ->label('Merchant')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->groupedBulkActions([
                //
            ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}

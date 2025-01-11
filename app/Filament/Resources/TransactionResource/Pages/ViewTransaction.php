<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Transaction Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PENDING' => 'warning',
                                'PROCESSING' => 'info',
                                'COMPLETED' => 'success',
                                'CANCELED' => 'danger',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'success' => 'success',
                                'failed' => 'danger',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('order.order_status')
                            ->label('Order Status')
                            ->badge()
                            ->color(fn (string $state): string => match (strtolower($state)) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'completed' => 'success',
                                'canceled' => 'danger',
                                default => 'secondary',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Transaction Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Transaction ID'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Customer'),
                        Infolists\Components\TextEntry::make('userLocation.address')
                            ->label('Delivery Address'),
                        Infolists\Components\TextEntry::make('order.id')
                            ->label('Order ID'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Transaction Date')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Order Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('order.orderItems')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Product'),
                                Infolists\Components\TextEntry::make('product.merchant.name')
                                    ->label('Merchant'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantity'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money('idr'),
                                Infolists\Components\TextEntry::make('total_price')
                                    ->label('Total')
                                    ->state(fn ($record) => $record->price * $record->quantity)
                                    ->money('idr'),
                            ])
                            ->columns(5),
                    ]),

                Infolists\Components\Section::make('Payment Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Total Payment')
                            ->money('idr'),
                        Infolists\Components\TextEntry::make('shipping_price')
                            ->label('Shipping Cost')
                            ->money('idr'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->badge(),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Delivery Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('courier.name')
                            ->label('Courier')
                            ->getStateUsing(function ($record) {
                                if (!$record->courier) {
                                    return 'Not assigned';
                                }
                                return $record->courier->full_details;
                            })
                            ->badge()
                            ->color(fn ($state) => $state === 'Not assigned' ? 'warning' : 'success'),
                        Infolists\Components\TextEntry::make('rating')
                            ->label('Rating')
                            ->visible(fn ($record) => !is_null($record->rating)),
                        Infolists\Components\TextEntry::make('note')
                            ->label('Notes')
                            ->visible(fn ($record) => !empty($record->note)),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

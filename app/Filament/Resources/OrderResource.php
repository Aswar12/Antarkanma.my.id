<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormsComponentsSelect::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                FormsComponentsTextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                FormsComponentsSelect::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->required(),
                FormsComponentsSelect::make('order_status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
            ]);
    }



    public static function getActions(): array
    {
        return [
            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->form([
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update(['order_status' => $data['status']]);
                    Notification::make()
                        ->title('Order status updated successfully')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('updatePaymentStatus')
                ->label('Update Payment Status')
                ->form([
                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                        ])
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update(['payment_status' => $data['payment_status']]);
                    Notification::make()
                        ->title('Payment status updated successfully')
                        ->success()
                        ->send();
                }),
        ];
    }

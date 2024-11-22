<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormsComponentsSelect::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                FormsComponentsSelect::make('user_location_id')
                    ->relationship('userLocation', 'address')
                    ->searchable()
                    ->required(),
                FormsComponentsTextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                FormsComponentsTextInput::make('shipping_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                FormsComponentsSelect::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                FormsComponentsSelect::make('payment')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'e_wallet' => 'E-Wallet',
                    ])
                    ->required(),
                FormsComponentsSelect::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->required(),
                FormsComponentsSelect::make('courier_id')
                    ->relationship('courier', 'name')
                    ->searchable(),
                FormsComponentsTextInput::make('rating')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
                FormsComponentsTextarea::make('note')
                    ->columnSpanFull(),
            ]);
    }
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
                ->action(function (Transaction $record, array $data): void {
                    $record->update(['status' => $data['status']]);
                    Notification::make()
                        ->title('Transaction status updated successfully')
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
                ->action(function (Transaction $record, array $data): void {
                    $record->update(['payment_status' => $data['payment_status']]);
                    Notification::make()
                        ->title('Payment status updated successfully')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('processMultiMerchantOrder')
                ->label('Process Multi-Merchant Order')
                ->action(function (Transaction $record): void {
                    $record->processMultiMerchantOrder();
                    Notification::make()
                        ->title('Multi-merchant order processed successfully')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }

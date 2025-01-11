<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Transaction Status')
                            ->options([
                                'PENDING' => 'Pending',
                                'PROCESSING' => 'Processing',
                                'COMPLETED' => 'Completed',
                                'CANCELED' => 'Canceled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'success' => 'Success',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('order.order_status')
                            ->label('Order Status')
                            ->options([
                                'PENDING' => 'Pending',
                                'PROCESSING' => 'Processing',
                                'COMPLETED' => 'Completed',
                                'CANCELED' => 'Canceled',
                            ])
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('user_location_id')
                            ->relationship('userLocation', 'address')
                            ->disabled(),
                        Forms\Components\TextInput::make('order_id')
                            ->label('Order ID')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('total_price')
                            ->disabled()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('shipping_price')
                            ->disabled()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'success' => 'Success',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'e_wallet' => 'E-Wallet',
                                'cod' => 'Cash on Delivery',
                            ])
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Delivery Information')
                    ->schema([
                        Forms\Components\Select::make('courier_id')
                            ->relationship(
                                'courier',
                                'id',
                                fn($query) => $query->with('user')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                "{$record->user->name} ({$record->vehicle_type} - {$record->license_plate})"
                            )
                            ->searchable()
                            ->preload()
                            ->label('Courier')
                            ->placeholder('No courier assigned')
                            ->helperText(function ($record) {
                                if (!$record || !$record->courier) {
                                    return 'No courier has been assigned to this order yet.';
                                }
                                return "Vehicle: {$record->courier->vehicle_type} ({$record->courier->license_plate})";
                            }),
                        Forms\Components\TextInput::make('rating')
                            ->disabled()
                            ->visible(fn($record) => !is_null($record->rating)),
                        Forms\Components\Textarea::make('note')
                            ->disabled()
                            ->visible(fn($record) => !empty($record->note)),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only allow updating payment_status and courier_id
        $originalData = $this->record->toArray();
        foreach ($data as $key => $value) {
            if (!in_array($key, ['payment_status', 'courier_id'])) {
                $data[$key] = $originalData[$key];
            }
        }
        return $data;
    }
}

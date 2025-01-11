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
use App\Filament\Resources\TransactionResource\RelationManagers\OrderRelationManager;
use App\Filament\Resources\TransactionResource\RelationManagers\OrderItemsRelationManager;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship(
                                'user',
                                'name',
                                fn (Builder $query) => $query->whereNotNull('name')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_location_id')
                            ->relationship(
                                'userLocation',
                                'address',
                                fn (Builder $query) => $query->whereNotNull('address')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('order_id')
                            ->label('Order ID')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Placeholder::make('orderItems')
                            ->content(function ($record) {
                                if (!$record || !$record->order) return 'No items';
                                
                                $items = $record->order->orderItems;
                                
                                return view('filament.components.order-items-list', [
                                    'items' => $items
                                ]);
                            }),
                    ]),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('shipping_price')
                            ->required()
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
                            ->required(),
                        Forms\Components\DateTimePicker::make('payment_date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Select::make('courier_id')
                            ->relationship(
                                'courier',
                                'id',
                                fn (Builder $query) => $query->with('user')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
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
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                        Forms\Components\Textarea::make('note')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Transaction ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.orderItems')
                    ->label('Order Items')
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) {
                        if (!$record || !$record->order) return [];
                        
                        $items = $record->order->orderItems;
                        if ($items->isEmpty()) return [];
                        
                        $itemsByMerchant = $items->groupBy('merchant_id');
                        
                        return $itemsByMerchant->map(function($items, $merchantId) {
                            $merchant = $items->first()->merchant;
                            if (!$merchant) return "Merchant not found";
                            
                            $total = number_format($items->sum(fn($item) => $item->price * $item->quantity), 0, ',', '.');
                            $itemCount = $items->count();
                            return "{$merchant->name} ({$itemCount} items) - Rp {$total}";
                        })->toArray();
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Payment')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->label('Shipping Cost')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('courier.user.name')
                    ->label('Courier')
                    ->getStateUsing(function ($record) {
                        if (!$record->courier) {
                            return 'Not assigned';
                        }
                        return "{$record->courier->user->name} ({$record->courier->vehicle_type} - {$record->courier->license_plate})";
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'Not assigned' ? 'warning' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Transaction Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderRelationManager::class,
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

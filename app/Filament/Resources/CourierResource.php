<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourierResource\Pages;
use App\Filament\Resources\CourierResource\RelationManagers;
use App\Models\Courier;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourierResource extends Resource
{
    protected static ?string $model = Courier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship(
                        'user',
                        'name',
                        fn (Builder $query) => $query->whereNot('roles', 'COURIER')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique('couriers', 'user_id')
                    ->label('Select User'),
                Forms\Components\TextInput::make('vehicle_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('license_plate')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('wallet_balance')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->minValue(0)
                    ->step(1000),
                Forms\Components\TextInput::make('fee_per_order')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(2000)
                    ->minValue(0)
                    ->step(500),
                Forms\Components\TextInput::make('minimum_balance')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(10000)
                    ->minValue(0)
                    ->step(1000),
                Forms\Components\Toggle::make('is_wallet_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Courier Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('wallet_balance')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_per_order')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_balance')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_wallet_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('topup')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(10000)
                            ->step(1000)
                            ->label('Top Up Amount'),
                    ])
                    ->action(function (Courier $record, array $data): void {
                        $record->topUpWallet($data['amount']);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Wallet topped up successfully')
                            ->body('Added Rp ' . number_format($data['amount'], 0, ',', '.'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Top Up Courier Wallet')
                    ->modalDescription('Enter the amount to add to the courier\'s wallet balance.')
                    ->modalSubmitActionLabel('Top Up')
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar'),
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
            //
        ];
    }

    protected static function beforeCreate(array $data): void
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->update(['roles' => 'COURIER']);
        }
    }

    protected static function afterDelete(Model $record): void
    {
        if ($record->user) {
            $record->user->update(['roles' => 'USER']);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCouriers::route('/'),
            'create' => Pages\CreateCourier::route('/create'),
            'edit' => Pages\EditCourier::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceFeeSettingResource\Pages;
use App\Filament\Resources\ServiceFeeSettingResource\RelationManagers;
use App\Models\ServiceFeeSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;

class ServiceFeeSettingResource extends Resource
{
    protected static ?string $model = ServiceFeeSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitle = 'Service Fee Setting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Fee Configuration')
                    ->description('Set the service fee amount charged per transaction (bukan per order!)')
                    ->schema([
                        Forms\Components\TextInput::make('service_fee')
                            ->label('Service Fee (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(500.00)
                            ->prefix('Rp')
                            ->helperText('Default: Rp 500 per transaksi (sekali per transaksi, bukan per order!)'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->required()
                            ->default(true)
                            ->helperText('Disable to temporarily turn off service fee'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Add notes about this fee change (optional)'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_fee')
                    ->label('Service Fee')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('updated_by')
                    ->label('Updated By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceFeeSettings::route('/'),
            'create' => Pages\CreateServiceFeeSetting::route('/create'),
            'edit' => Pages\EditServiceFeeSetting::route('/{record}/edit'),
        ];
    }
    
    /**
     * Only show one active setting at a time
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->where('is_active', true);
    }
}

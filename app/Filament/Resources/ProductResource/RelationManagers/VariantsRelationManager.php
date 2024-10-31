<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Tipe Variant')
                    ->options([
                        'size' => 'Ukuran',
                        'color' => 'Warna',
                        'material' => 'Material',
                        'style' => 'Model',
                    ])
                    ->required()
                    ->live(),

                // Kondisional Field berdasarkan tipe variant
                Forms\Components\Select::make('value')
                    ->label('Nilai')
                    ->options(function (Forms\Get $get) {
                        return match ($get('name')) {
                            'size' => [
                                'XS' => 'Extra Small',
                                'S' => 'Small',
                                'M' => 'Medium',
                                'L' => 'Large',
                                'XL' => 'Extra Large',
                                'XXL' => '2XL',
                            ],
                            'color' => [
                                'RED' => 'Merah',
                                'BLUE' => 'Biru',
                                'GREEN' => 'Hijau',
                                'BLACK' => 'Hitam',
                                'WHITE' => 'Putih',
                            ],
                            'material' => [
                                'COTTON' => 'Katun',
                                'POLYESTER' => 'Polyester',
                                'WOOL' => 'Wol',
                                'SILK' => 'Sutra',
                                'LEATHER' => 'Kulit',
                            ],
                            'style' => [
                                'CASUAL' => 'Kasual',
                                'FORMAL' => 'Formal',
                                'SPORT' => 'Olahraga',
                                'VINTAGE' => 'Vintage',
                            ],
                            default => [],
                        };
                    })
                    ->required(),

                Forms\Components\TextInput::make('price_adjustment')
                    ->label('Penyesuaian Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tipe')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'size' => 'Ukuran',
                        'color' => 'Warna',
                        'material' => 'Material',
                        'style' => 'Model',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai'),
                Tables\Columns\TextColumn::make('price_adjustment')
                    ->label('Penyesuaian Harga')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'inactive',
                        'success' => 'active',
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

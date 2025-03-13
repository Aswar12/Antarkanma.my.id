<?php

namespace App\Filament\Resources\MerchantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Product Information')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Produk')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('price')
                                ->label('Harga')
                                ->required()
                                ->numeric()
                                ->prefix('Rp'),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->relationship('category', 'name')
                                ->required(),

                            Forms\Components\Toggle::make('is_available')
                                ->label('Tersedia')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger'),
                        ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Section::make('Product Images')
                    ->schema([
                        FileUpload::make('galleries.*.image')
                            ->label('Gambar Produk')
                            ->image()
                            ->multiple()
                            ->disk('s3')
                            ->directory('products/images')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('galleries.0.image')
                    ->label('Image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All Products')
                    ->trueLabel('Available Products')
                    ->falseLabel('Unavailable Products'),
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

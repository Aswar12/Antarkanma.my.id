<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\ProductCategoriesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ReviewsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Produk';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Produk')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Produk')
                            ->schema([
                                Forms\Components\Select::make('merchant_id')
                                    ->relationship('merchant', 'name')
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Select::make("category_id")
                                    ->relationship("category", "name")
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make("name")
                                            ->required(),
                                        Forms\Components\Textarea::make("description")
                                    ])
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->rows(4),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'ACTIVE' => 'Active',
                                        'INACTIVE' => 'Inactive',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('Galeri Produk')
                            ->schema([
                                Forms\Components\FileUpload::make('galleries')
                                    ->multiple()
                                    ->image()
                                    ->disk('s3')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1080')
                                    ->directory('products/images')
                                    ->visibility('public')
                                    ->downloadable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120) // 5MB
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file): string => 
                                            $this->getRecord()->id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension()
                                    ),
                            ]),
                        Forms\Components\Tabs\Tab::make('Varian Produk')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('value')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('price_adjustment')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                            ])
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Varian')
                                    ->reorderableWithButtons()
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(?Product $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(?Product $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('galleries.url')
                    ->label('Image')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->disk('s3'),
                Tables\Columns\TextColumn::make('merchant.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("category.name")
                    ->sortable()
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\GalleriesRelationManager::class,
            RelationManagers\ProductCategoriesRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

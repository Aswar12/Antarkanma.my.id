<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLocationResource\Pages;
use App\Filament\Widgets\UserLocationsMap;
use App\Models\UserLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserLocationResource extends Resource
{
    protected static ?string $model = UserLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'User Locations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Location Details')
                    ->schema([
                        Forms\Components\Select::make('address_type')
                            ->options([
                                'Rumah' => 'Rumah',
                                'Kantor' => 'Kantor',
                                'Apartemen' => 'Apartemen',
                                'Kos' => 'Kos',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('district')
                            ->label('Kecamatan')
                            ->required(),

                        Forms\Components\TextInput::make('city')
                            ->label('Kota')
                            ->required(),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->required(),

                        Forms\Components\TextInput::make('country')
                            ->default('Indonesia')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Map Location')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->required()
                                    ->default(-6.200000)
                                    ->step(0.000001),

                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->required()
                                    ->default(106.816666)
                                    ->step(0.000001),
                            ])
                            ->columns(2),

                        // Map preview using iframe
                        Forms\Components\View::make('filament.forms.components.map-preview')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Toggle::make('is_default')
                            ->label('Set as Default Address')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Rumah' => 'success',
                        'Kantor' => 'info',
                        'Apartemen' => 'warning',
                        'Kos' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('address')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('city')
                    ->label('Kota'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                // Custom column for map link
                Tables\Columns\TextColumn::make('map_link')
                    ->label('Map')
                    ->html()
                    ->formatStateUsing(fn ($record) => 
                        "<a href='https://www.google.com/maps?q={$record->latitude},{$record->longitude}' 
                            target='_blank' 
                            class='text-primary-600 hover:text-primary-900'>
                            View Map
                        </a>"
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('address_type')
                    ->options([
                        'Rumah' => 'Rumah',
                        'Kantor' => 'Kantor',
                        'Apartemen' => 'Apartemen',
                        'Kos' => 'Kos',
                        'Lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Address'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserLocations::route('/'),
            'create' => Pages\CreateUserLocation::route('/create'),
            'edit' => Pages\EditUserLocation::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            UserLocationsMap::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserLocationsMap::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user.name',
            'customer_name',
            'address',
            'city',
            'district',
            'postal_code',
        ];
    }
}

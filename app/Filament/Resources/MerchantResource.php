<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Filament\Resources\MerchantResource\RelationManagers;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Merchants';
    protected static ?string $modelLabel = 'Merchant';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Merchant Information')
                    ->description('Basic merchant details')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Merchant')
                                ->required()
                                ->maxLength(255),

                            Select::make('owner_id')
                                ->label('Owner')
                                ->options(function () {
                                    // Get users who either have MERCHANT role or don't have a merchant yet
                                    return User::where(function ($query) {
                                        $query->where('roles', 'MERCHANT')
                                            ->orWhereDoesntHave('merchant');
                                    })->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->afterStateUpdated(function ($state, $set) {
                                    // Update user role to MERCHANT when selected
                                    if ($state) {
                                        User::where('id', $state)->update(['roles' => 'MERCHANT']);

                                        // Get and set the phone number from the selected user
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('phone_number', $user->phone_number);
                                        }
                                    }
                                }),

                            Forms\Components\TextInput::make('phone_number')
                                ->label('Nomor Telepon')
                                ->tel()
                                ->disabled()
                                ->dehydrated(true),

                            Toggle::make('status')
                                ->label('Status Aktif')
                                ->onColor('success')
                                ->offColor('danger')
                                ->default('active'),
                        ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                Section::make('Operating Hours')
                    ->description('Set merchant operating schedule')
                    ->schema([
                        Grid::make(2)->schema([
                            TimePicker::make('opening_time')
                                ->label('Jam Buka')
                                ->required()
                                ->seconds(false),

                            TimePicker::make('closing_time')
                                ->label('Jam Tutup')
                                ->required()
                                ->seconds(false),

                            Select::make('operating_days')
                                ->label('Hari Operasional')
                                ->multiple()
                                ->options([
                                    'monday' => 'Senin',
                                    'tuesday' => 'Selasa',
                                    'wednesday' => 'Rabu',
                                    'thursday' => 'Kamis',
                                    'friday' => 'Jumat',
                                    'saturday' => 'Sabtu',
                                    'sunday' => 'Minggu',
                                ])
                                ->required(),
                        ]),
                    ]),

                Section::make('Location')
                    ->description('Merchant location coordinates')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('latitude')
                                ->label('Latitude')
                                ->required()
                                ->numeric()
                                ->default(0),

                            Forms\Components\TextInput::make('longitude')
                                ->label('Longitude')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->circular()
                    ->size(100)
                    ->defaultImageUrl(url('/images/default-merchant.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Merchant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Nomor Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('opening_time')
                    ->label('Jam Buka')
                    ->dateTime('H:i'),

                Tables\Columns\TextColumn::make('closing_time')
                    ->label('Jam Tutup')
                    ->dateTime('H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('toggleStatus')
                    ->label(fn (Merchant $record): string => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn (Merchant $record): string => $record->status === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Merchant $record): string => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Merchant $record): void {
                        $record->update([
                            'status' => $record->status === 'active' ? 'inactive' : 'active'
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            // Begin transaction
                            DB::beginTransaction();
                            try {
                                foreach ($records as $record) {
                                    // Reset the owner's role to USER if they don't have other merchants
                                    $owner = $record->owner;
                                    if ($owner && $owner->merchant()->count() <= 1) {
                                        $owner->update(['roles' => 'USER']);
                                    }
                                    $record->delete();
                                }
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                throw $e;
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMerchants::route('/'),
            'create' => Pages\CreateMerchant::route('/create'),
            'edit' => Pages\EditMerchant::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}

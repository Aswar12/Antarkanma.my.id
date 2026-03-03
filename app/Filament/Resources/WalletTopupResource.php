<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTopupResource\Pages;
use App\Filament\Resources\WalletTopupResource\RelationManagers;
use App\Models\WalletTopup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class WalletTopupResource extends Resource
{
    protected static ?string $model = WalletTopup::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    
    protected static ?string $navigationGroup = 'Wallet Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Topup Information')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\TextInput::make('unique_code')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('transfer_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\FileUpload::make('payment_proof')
                            ->disk('public')
                            ->directory('topup-proofs')
                            ->visibility('public')
                            ->label('Bukti Transfer')
                            ->downloadable()
                            ->openable(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Status & Verification')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                WalletTopup::STATUS_PENDING => 'Pending',
                                WalletTopup::STATUS_VERIFIED => 'Verified',
                                WalletTopup::STATUS_APPROVED => 'Approved',
                                WalletTopup::STATUS_REJECTED => 'Rejected',
                            ])
                            ->disabled(),
                        Forms\Components\Toggle::make('bank_notification_matched')
                            ->label('Bank Notification Matched')
                            ->disabled(),
                        Forms\Components\Select::make('verified_by')
                            ->relationship('verifier', 'name')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->disabled(),
                        Forms\Components\Textarea::make('admin_note')
                            ->rows(3)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('courier.user.name')
                    ->label('Courier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_code')
                    ->label('Kode Unik')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transfer_amount')
                    ->money('IDR')
                    ->label('Total Transfer')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'info' => 'VERIFIED',
                        'success' => 'APPROVED',
                        'danger' => 'REJECTED',
                    ]),
                Tables\Columns\IconColumn::make('bank_notification_matched')
                    ->label('Bank Matched')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Waktu Transfer'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        WalletTopup::STATUS_PENDING => 'Pending',
                        WalletTopup::STATUS_VERIFIED => 'Verified',
                        WalletTopup::STATUS_APPROVED => 'Approved',
                        WalletTopup::STATUS_REJECTED => 'Rejected',
                    ])
                    ->label('Filter by Status'),
                Tables\Filters\TernaryFilter::make('bank_notification_matched')
                    ->label('Bank Notification Matched'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('✅ Verify & Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (WalletTopup $record): bool => $record->status === WalletTopup::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Topup')
                    ->modalDescription(function (WalletTopup $record): string {
                        return "Pastikan notifikasi bank menampilkan:\n\n" .
                               "💰 Nominal: Rp " . number_format($record->transfer_amount, 0, ',', '.') . "\n" .
                               "👤 Dari: " . $record->courier->user->name . "\n" .
                               "⏰ Waktu: " . $record->created_at->format('H:i');
                    })
                    ->form([
                        Forms\Components\Placeholder::make('instructions')
                            ->label('Langkah Verifikasi:')
                            ->content(function (WalletTopup $record) {
                                return "1. Buka aplikasi M-Banking Anda\n" .
                                       "2. Cek mutasi/rekening koran\n" .
                                       "3. Cari transaksi dengan nominal Rp " . number_format($record->transfer_amount, 0, ',', '.') . "\n" .
                                       "4. Pastikan dari " . $record->courier->user->name . "\n" .
                                       "5. Jika cocok, klik 'Approve' di bawah";
                            })
                            ->columnSpanFull(),
                    ])
                    ->action(function (WalletTopup $record): void {
                        $record->approve(auth()->id());
                        
                        Notification::make()
                            ->success()
                            ->title('Topup Approved')
                            ->body('Saldo courier telah ditambahkan sebesar Rp ' . number_format($record->amount, 0, ',', '.'))
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('❌ Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (WalletTopup $record): bool => $record->status === WalletTopup::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Topup')
                    ->modalDescription('Yakin ingin menolak topup ini? Courier akan diberi notifikasi.')
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->required()
                            ->label('Alasan Penolakan')
                            ->placeholder('Contoh: Nominal tidak sesuai, bukti tidak jelas, dll')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (WalletTopup $record, array $data): void {
                        $record->reject(auth()->id(), $data['admin_note']);
                        
                        Notification::make()
                            ->warning()
                            ->title('Topup Rejected')
                            ->body('Topup telah ditolak. Courier telah diberi notifikasi.')
                            ->send();
                    }),
                
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListWalletTopups::route('/'),
            // 'create' => Pages\CreateWalletTopup::route('/create'),
            'view' => Pages\ViewWalletTopup::route('/{record}'),
            // 'edit' => Pages\EditWalletTopup::route('/{record}/edit'),
        ];
    }
}

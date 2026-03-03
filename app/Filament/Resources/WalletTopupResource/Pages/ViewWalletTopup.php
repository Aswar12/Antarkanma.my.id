<?php

namespace App\Filament\Resources\WalletTopupResource\Pages;

use App\Filament\Resources\WalletTopupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWalletTopup extends ViewRecord
{
    protected static string $resource = WalletTopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Topup')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->status === \App\Models\WalletTopup::STATUS_PENDING)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->approve(auth()->id());
                    
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Topup Approved')
                        ->body('Saldo courier telah ditambahkan.')
                        ->send();
                }),
            
            Actions\Action::make('reject')
                ->label('Reject Topup')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->status === \App\Models\WalletTopup::STATUS_PENDING)
                ->requiresConfirmation()
                ->modalHeading('Tolak Topup')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_note')
                        ->required()
                        ->label('Alasan Penolakan')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->reject(auth()->id(), $data['admin_note']);
                    
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title('Topup Rejected')
                        ->body('Topup telah ditolak.')
                        ->send();
                }),
        ];
    }
}

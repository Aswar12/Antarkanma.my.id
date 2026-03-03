<?php

namespace App\Filament\Resources\WalletTopupResource\Pages;

use App\Filament\Resources\WalletTopupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWalletTopups extends ListRecords
{
    protected static string $resource = WalletTopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - topups are created by couriers via API
        ];
    }
}

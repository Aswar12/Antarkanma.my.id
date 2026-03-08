<?php

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMerchant extends EditRecord
{
    protected static string $resource = MerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle logo upload - Filament returns array for file uploads
        if (isset($data['logo']) && is_array($data['logo']) && !empty($data['logo'])) {
            // Get the first uploaded file (Filament stores as array)
            $data['logo'] = $data['logo'][0];
        }

        return $data;
    }
}

<?php

namespace App\Filament\Resources\UserLocationResource\Pages;

use App\Filament\Resources\UserLocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserLocation extends CreateRecord
{
    protected static string $resource = UserLocationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If this is set as default, unset all other default addresses for this user
        if ($data['is_default'] ?? false) {
            \App\Models\UserLocation::where('user_id', $data['user_id'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}

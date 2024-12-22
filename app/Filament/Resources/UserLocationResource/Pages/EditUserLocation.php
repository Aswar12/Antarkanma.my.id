<?php

namespace App\Filament\Resources\UserLocationResource\Pages;

use App\Filament\Resources\UserLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserLocation extends EditRecord
{
    protected static string $resource = UserLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this is set as default, unset all other default addresses for this user
        if ($data['is_default'] ?? false) {
            \App\Models\UserLocation::where('user_id', $data['user_id'])
                ->where('id', '!=', $this->record->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}

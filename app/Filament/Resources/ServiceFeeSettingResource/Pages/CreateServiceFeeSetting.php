<?php

namespace App\Filament\Resources\ServiceFeeSettingResource\Pages;

use App\Filament\Resources\ServiceFeeSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceFeeSetting extends CreateRecord
{
    protected static string $resource = ServiceFeeSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['service_fee'] = $data['service_fee'] ?? 500.00;
        $data['updated_by'] = auth()->user()->name ?? null;
        
        return $data;
    }
}

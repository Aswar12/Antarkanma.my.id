<?php

namespace App\Filament\Resources\ServiceFeeSettingResource\Pages;

use App\Filament\Resources\ServiceFeeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceFeeSetting extends EditRecord
{
    protected static string $resource = ServiceFeeSettingResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['service_fee'] = $data['service_fee'] ?? 500.00;
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['service_fee'] = $data['service_fee'] ?? 500.00;
        $data['updated_by'] = auth()->user()->name ?? null;
        
        return $data;
    }
}

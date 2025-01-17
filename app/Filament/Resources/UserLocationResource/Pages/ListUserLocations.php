<?php

namespace App\Filament\Resources\UserLocationResource\Pages;

use App\Filament\Resources\UserLocationResource;
use App\Filament\Widgets\UserLocationsMap;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserLocations extends ListRecords
{
    protected static string $resource = UserLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New Location'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserLocationsMap::class,
        ];
    }
}

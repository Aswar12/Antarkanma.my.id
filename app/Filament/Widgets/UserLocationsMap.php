<?php

namespace App\Filament\Widgets;

use App\Models\UserLocation;
use Filament\Widgets\Widget;

class UserLocationsMap extends Widget
{
    protected static string $view = 'filament.widgets.user-locations-map';
    
    protected int | string | array $columnSpan = 'full';

    public function getLocations()
    {
        return UserLocation::query()
            ->where('is_active', true)
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'lat' => (float) $location->latitude,
                    'lng' => (float) $location->longitude,
                    'title' => $location->customer_name,
                    'address_type' => $location->address_type,
                ];
            });
    }
}

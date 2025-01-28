<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\UserLocation;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MerchantLocationsMap extends Widget
{
    protected static string $view = 'filament.widgets.merchant-locations-map';
    
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public function getMerchantLocations()
    {
        return Cache::store('array')->remember('merchant_locations', 60, function () {
            return User::whereHas('merchant')
                ->with(['merchant', 'locations' => function ($query) {
                    $query->where('is_default', true)
                        ->where('is_active', true);
                }])
                ->get()
                ->map(function ($user) {
                    $location = $user->locations->first();
                    if (!$location) return null;

                    // Get the full URL for the logo
                    $logoUrl = null;
                    if ($user->merchant->logo) {
                        $logoUrl = Storage::disk('s3')->url($user->merchant->logo);
                    } elseif ($user->profile_photo_path) {
                        $logoUrl = Storage::disk('s3')->url($user->profile_photo_path);
                    }

                    return [
                        'name' => $user->merchant->name,
                        'address' => $location->address,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'district' => $location->district,
                        'logo' => $logoUrl,
                    ];
                })
                ->filter()
                ->values();
        });
    }

    protected function getViewData(): array
    {
        return [
            'merchantLocations' => $this->getMerchantLocations(),
        ];
    }
}

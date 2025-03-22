<?php

namespace App\Filament\Widgets;

use App\Models\User;
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
                ->with('merchant')
                ->get()
                ->map(function ($user) {
                    if (!$user->merchant->latitude || !$user->merchant->longitude) return null;

                    // Get the full URL for the logo
                    $logoUrl = null;
                    if ($user->merchant->logo) {
                        $logoUrl = Storage::disk('s3')->url($user->merchant->logo);
                    } elseif ($user->profile_photo_path) {
                        $logoUrl = Storage::disk('s3')->url($user->profile_photo_path);
                    }

                    return [
                        'name' => $user->merchant->name,
                        'address' => $user->merchant->address,
                        'latitude' => $user->merchant->latitude,
                        'longitude' => $user->merchant->longitude,
                        'district' => $user->merchant->district,
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

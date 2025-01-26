<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Disable Filament's cache
        config(['filament.cache.enabled' => false]);
        config(['cache.default' => 'array']);
    }
}

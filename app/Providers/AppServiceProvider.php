<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;
use App\Services\FirebaseService;
use App\Services\OsrmService;
use App\Http\Controllers\API\ShippingController;
use Kreait\Firebase\Contract\Messaging;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register OSRM Service
        $this->app->singleton(OsrmService::class, function ($app) {
            return new OsrmService();
        });

        // Register ShippingController
        $this->app->singleton(ShippingController::class, function ($app) {
            return new ShippingController($app->make(OsrmService::class));
        });

        // Register Firebase Service wrapper
        $this->app->singleton(FirebaseService::class, function ($app) {
            $messaging = null;

            // Only create Messaging instance if Firebase is configured
            if (config('firebase.projects.app.credentials') &&
                file_exists(config('firebase.projects.app.credentials'))) {
                try {
                    $messaging = (new \Kreait\Firebase\Factory)
                        ->withServiceAccount(config('firebase.projects.app.credentials'))
                        ->createMessaging();
                } catch (\Exception $e) {
                    \Log::warning('Failed to initialize Firebase Messaging: ' . $e->getMessage());
                }
            }

            return new FirebaseService($messaging);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
    }
}

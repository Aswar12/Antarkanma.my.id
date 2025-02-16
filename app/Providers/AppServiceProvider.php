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

        // Only register Firebase services if both project ID and credentials are available
        if (config('firebase.project_id') && 
            config('firebase.credentials.file') && 
            file_exists(config('firebase.credentials.file'))) {
            
            // Register Firebase Messaging service
            $this->app->singleton(Messaging::class, function ($app) {
                return (new \Kreait\Firebase\Factory)
                    ->withServiceAccount(config('firebase.credentials.file'))
                    ->withProjectId(config('firebase.project_id'))
                    ->createMessaging();
            });

            // Register Firebase Service wrapper
            $this->app->singleton(FirebaseService::class, function ($app) {
                return new FirebaseService(
                    $app->make(Messaging::class)
                );
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
    }
}

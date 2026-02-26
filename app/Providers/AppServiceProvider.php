<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\Merchant;
use App\Observers\ProductObserver;
use App\Observers\MerchantObserver;
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

            // Get credentials path from config
            $credentialsPath = config('firebase.projects.app.credentials');

            // Fix for local development: ensure we use absolute path
            if ($credentialsPath && !file_exists($credentialsPath)) {
                // Try to resolve relative path from base_path
                $credentialsPath = base_path('storage/app/firebase/firebase-credentials.json');
            }

            // Override GOOGLE_APPLICATION_CREDENTIALS for local development
            // This prevents kreait from looking at Docker paths (/app/...)
            if ($credentialsPath && file_exists($credentialsPath)) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $credentialsPath;
                $_SERVER['GOOGLE_APPLICATION_CREDENTIALS'] = $credentialsPath;

                try {
                    $messaging = (new \Kreait\Firebase\Factory)
                        ->withServiceAccount($credentialsPath)
                        ->createMessaging();

                    \Log::info('Firebase Messaging initialized successfully with credentials: ' . $credentialsPath);
                } catch (\Exception $e) {
                    // \Log::warning('Failed to initialize Firebase Messaging: ' . $e->getMessage());
                }
            } else {
                \Log::warning('Firebase credentials file not found at: ' . $credentialsPath);
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
        Merchant::observe(MerchantObserver::class);
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\TestStorageLocations;
use App\Console\Commands\TestObjectStorage;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        TestObjectStorage::class,
        TestStorageLocations::class,
        Commands\TestFirebaseNotification::class,
        Commands\MigrateProductImagesToS3::class,
        Commands\SetupS3Folders::class,
        Commands\ResetAndSeedWithS3Images::class,
        Commands\ResetAndSeedDatabase::class,
        Commands\TestS3Upload::class,
        Commands\CancelTimedOutTransactions::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check and cancel timed out transactions every minute
        $schedule->command('transactions:cancel-timed-out')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

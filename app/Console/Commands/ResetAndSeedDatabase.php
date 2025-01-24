<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class ResetAndSeedDatabase extends Command
{
    protected $signature = 'db:reset-and-seed';
    protected $description = 'Reset database and seed with test accounts and S3 storage setup';

    public function handle()
    {
        $this->info('Starting complete database reset and seed...');

        try {
            // Step 1: Fresh migration
            $this->info("\nStep 1: Running fresh migrations...");
            Artisan::call('migrate:fresh');
            $this->info("✓ Migrations completed");

            // Step 2: Setup S3 folders
            $this->info("\nStep 2: Setting up S3 folders...");
            Artisan::call('storage:setup-s3-folders');
            $this->info("✓ S3 folders created");

            // Step 3: Copy existing images from local storage to S3
            $this->info("\nStep 3: Copying existing product images to S3...");
            $localPath = storage_path('app/public/product-galleries');
            $files = glob($localPath . '/*.jpg');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $s3Path = 'products/galleries/' . $filename;
                
                if (file_exists($file)) {
                    Storage::disk('public')->put($s3Path, file_get_contents($file));
                    $this->info("  ✓ Copied: {$filename}");
                }
            }

            // Step 4: Run TestAccountsSeeder
            $this->info("\nStep 4: Running TestAccountsSeeder...");
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TestAccountsSeeder']);
            $this->info("✓ Test accounts created");

            $this->info("\nDatabase reset and seed completed successfully!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during reset and seed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

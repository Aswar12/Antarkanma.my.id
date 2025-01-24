<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductGallery;
use Illuminate\Support\Facades\DB;

class MigrateProductImagesToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3';
    protected $description = 'Migrate existing product images from local storage to S3';

    public function handle()
    {
        $this->info('Starting migration of product images to S3...');

        try {
            // Get all files from local public storage
            $files = Storage::disk('local')->allFiles('public');
            $totalFiles = count($files);
            $this->info("Found {$totalFiles} files to migrate.");

            DB::beginTransaction();

            $bar = $this->output->createProgressBar($totalFiles);
            $migratedCount = 0;

            foreach ($files as $file) {
                // Skip the .gitignore file
                if (basename($file) === '.gitignore') {
                    $bar->advance();
                    continue;
                }

                // Get the file contents from local storage
                $contents = Storage::disk('local')->get($file);

                // Remove 'public/' from the path for S3
                $s3Path = str_replace('public/', '', $file);

                // Upload to S3
                Storage::disk('public')->put($s3Path, $contents);

                // Update database records that reference this file
                $localUrl = str_replace('public/', '', $file);
                $galleries = ProductGallery::where('url', 'LIKE', '%' . $localUrl . '%')->get();

                foreach ($galleries as $gallery) {
                    $gallery->url = $s3Path;
                    $gallery->save();
                }

                $migratedCount++;
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->newLine();
            $this->info("\nSuccessfully migrated {$migratedCount} files to S3!");
            $this->info('Database records have been updated with new S3 URLs.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error migrating files: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

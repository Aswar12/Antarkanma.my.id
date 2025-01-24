<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Upload extends Command
{
    protected $signature = 'storage:test-s3-upload';
    protected $description = 'Test S3 upload to IDCloudHost';

    public function handle()
    {
        $this->info('Testing S3 upload to IDCloudHost...');

        try {
            // First, let's try to upload a text file
            $content = 'Test file content - ' . now();
            $testFilePath = 'products/test/test-' . time() . '.txt';

            $this->info('1. Uploading text file...');
            Storage::disk('s3')->put($testFilePath, $content);
            $textUrl = Storage::disk('s3')->url($testFilePath);
            $this->info('✓ Text file uploaded');
            $this->info('Text file URL: ' . $textUrl);

            // Now, let's try to upload an image from local storage
            $this->info("\n2. Uploading image file...");
            $localImagePath = storage_path('app/public/product-galleries/pexels-kelvinocta16-7190355.jpg');
            
            if (file_exists($localImagePath)) {
                $imageContent = file_get_contents($localImagePath);
                $s3ImagePath = 'products/galleries/test-image-' . time() . '.jpg';
                
                Storage::disk('s3')->put($s3ImagePath, $imageContent);
                $imageUrl = Storage::disk('s3')->url($s3ImagePath);
                
                $this->info('✓ Image file uploaded');
                $this->info('Image file URL: ' . $imageUrl);
            } else {
                $this->warn('Sample image not found at: ' . $localImagePath);
            }

            $this->info("\nPlease verify these files in IDCloudHost console:");
            $this->info("Bucket: " . config('filesystems.disks.s3.bucket'));
            $this->info("Region: " . config('filesystems.disks.s3.region'));
            $this->info("Endpoint: " . config('filesystems.disks.s3.endpoint'));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error uploading to S3: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

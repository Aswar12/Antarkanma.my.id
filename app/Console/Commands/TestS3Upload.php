<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Upload extends Command
{
    protected $signature = 'test:s3';
    protected $description = 'Test S3 upload functionality';

    public function handle()
    {
        try {
            $this->info('S3 Configuration:');
            $this->info('Endpoint: ' . config('filesystems.disks.s3.endpoint'));
            $this->info('Bucket: ' . config('filesystems.disks.s3.bucket'));
            $this->info('Region: ' . config('filesystems.disks.s3.region'));
            $this->info('Directory: ' . env('AWS_DIRECTORY'));
            
            // Get S3 client
            $s3Client = Storage::disk('s3')->getClient();
            
            // Test bucket access
            try {
                $this->info('Testing bucket access...');
                $s3Client->listBuckets();
                $this->info('Bucket access successful');
            } catch (\Exception $e) {
                $this->error('Bucket access failed: ' . $e->getMessage());
                return;
            }
            
            // Try to upload with explicit ACL
            $path = trim(env('AWS_DIRECTORY'), '/') . '/test.txt';
            $this->info('Attempting to upload to path: ' . $path);
            
            // Try direct Storage facade upload first
            $this->info('Attempting Storage facade upload...');
            $result = Storage::disk('s3')->put($path, 'Testing S3 upload');
            
            if ($result) {
                $this->info('Storage facade upload successful');
                $url = Storage::disk('s3')->url($path);
                $this->info('File URL: ' . $url);
                
                // List directory contents
                $this->info('Listing directory contents:');
                $files = Storage::disk('s3')->files(env('AWS_DIRECTORY'));
                foreach ($files as $file) {
                    $this->info('- ' . $file);
                }
            } else {
                $this->error('Storage facade upload failed');
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Error type: ' . get_class($e));
            if (method_exists($e, 'getAwsErrorCode')) {
                $this->error('AWS Error Code: ' . $e->getAwsErrorCode());
            }
            if (method_exists($e, 'getAwsErrorType')) {
                $this->error('AWS Error Type: ' . $e->getAwsErrorType());
            }
        }
    }
}

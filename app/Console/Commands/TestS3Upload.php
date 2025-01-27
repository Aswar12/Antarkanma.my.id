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
            // Try to upload the test file
            $result = Storage::disk('s3')->put('test.txt', 'Testing S3 upload');
            
            if ($result) {
                $this->info('Successfully uploaded to S3');
                
                // Try to get the URL
                $url = Storage::disk('s3')->url('test.txt');
                $this->info("File URL: " . $url);
                
                // Verify file exists
                $exists = Storage::disk('s3')->exists('test.txt');
                $this->info("File exists check: " . ($exists ? 'Yes' : 'No'));
            } else {
                $this->error('Failed to upload to S3');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

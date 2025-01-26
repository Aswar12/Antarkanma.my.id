<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestObjectStorage extends Command
{
    protected $signature = 'storage:test';
    protected $description = 'Test object storage configuration';

    public function handle()
    {
        try {
            // Create a test file
            $content = 'Test file content - ' . now();
            Storage::disk('s3')->put('test.txt', $content);
            $this->info('✓ Successfully uploaded test file');

            // Read the file
            $readContent = Storage::disk('s3')->get('test.txt');
            $this->info('✓ Successfully read file content: ' . $readContent);

            // Get the URL
            $url = Storage::disk('s3')->url('test.txt');
            $this->info('✓ File URL: ' . $url);

            // Delete the test file
            Storage::disk('s3')->delete('test.txt');
            $this->info('✓ Successfully deleted test file');

            $this->info('All storage tests passed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Storage test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

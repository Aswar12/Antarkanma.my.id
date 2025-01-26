<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestStorageLocations extends Command
{
    protected $signature = 'storage:test-locations';
    protected $description = 'Test both public and S3 storage locations';

    public function handle()
    {
        try {
            // Test public storage
            $publicContent = 'Test public storage content - ' . now();
            Storage::disk('public')->put('test-public.txt', $publicContent);
            $this->info('✓ Successfully created file in public storage');
            $this->info('  Path: ' . storage_path('app/public/test-public.txt'));
            $this->info('  URL: ' . Storage::disk('public')->url('test-public.txt'));

            // Test S3 storage
            $s3Content = 'Test S3 storage content - ' . now();
            Storage::disk('s3')->put('test-s3.txt', $s3Content);
            $this->info('✓ Successfully created file in S3 storage');
            $this->info('  URL: ' . Storage::disk('s3')->url('test-s3.txt'));

            // Show how to use both storages
            $this->info("\nHow to use in code:");
            $this->info("1. For public storage (storage/app/public):");
            $this->info('   Storage::disk(\'public\')->put(\'filename.ext\', $content);');
            $this->info('   Storage::disk(\'public\')->url(\'filename.ext\');');
            
            $this->info("\n2. For S3 storage:");
            $this->info('   Storage::disk(\'s3\')->put(\'filename.ext\', $content);');
            $this->info('   Storage::disk(\'s3\')->url(\'filename.ext\');');

            // Clean up test files
            Storage::disk('public')->delete('test-public.txt');
            Storage::disk('s3')->delete('test-s3.txt');
            $this->info("\n✓ Test files cleaned up");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetupS3Folders extends Command
{
    protected $signature = 'storage:setup-s3-folders';
    protected $description = 'Setup folder structure in S3 storage';

    private $folders = [
        'products' => [
            'description' => 'Product images',
            'subfolders' => [
                'galleries' => 'Product gallery images',
                'thumbnails' => 'Product thumbnail images'
            ]
        ],
        'merchants' => [
            'description' => 'Merchant related files',
            'subfolders' => [
                'logos' => 'Merchant logo images',
                'banners' => 'Merchant banner images'
            ]
        ],
        'users' => [
            'description' => 'User related files',
            'subfolders' => [
                'profiles' => 'User profile images',
                'documents' => 'User documents'
            ]
        ],
        'categories' => [
            'description' => 'Category related images',
            'subfolders' => [
                'icons' => 'Category icon images',
                'banners' => 'Category banner images'
            ]
        ]
    ];

    public function handle()
    {
        $this->info('Setting up S3 folder structure...');

        try {
            foreach ($this->folders as $folder => $config) {
                $this->info("\nCreating {$folder} folder structure:");
                $this->info("└── {$folder}/ ({$config['description']})");

                // Create main folder with a .keep file
                Storage::disk('public')->put("{$folder}/.keep", '');

                // Create subfolders
                foreach ($config['subfolders'] as $subfolder => $description) {
                    Storage::disk('public')->put("{$folder}/{$subfolder}/.keep", '');
                    $this->info("    └── {$subfolder}/ ({$description})");
                }
            }

            $this->info("\nS3 folder structure setup completed successfully!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error setting up S3 folders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

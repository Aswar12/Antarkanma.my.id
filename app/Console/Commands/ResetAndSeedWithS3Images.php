<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\Merchant;
use App\Models\User;
use App\Models\ProductCategory;

class ResetAndSeedWithS3Images extends Command
{
    protected $signature = 'db:reset-seed-s3';
    protected $description = 'Reset database and seed with new data using S3 storage';

    private $sampleImages = [
        'food' => [
            'https://images.unsplash.com/photo-1512058564366-18510be2db19',
            'https://images.unsplash.com/photo-1645696301019-35a80c495d90',
            'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58'
        ],
        'drink' => [
            'https://images.unsplash.com/photo-1556679343-c7306c1976bc',
            'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4'
        ],
        'snack' => [
            'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d',
            'https://images.unsplash.com/photo-1628293211829-0a796f8b1cbb'
        ]
    ];

    private $merchantLogos = [
        'https://images.unsplash.com/photo-1621905252507-b35492cc74b4',
        'https://images.unsplash.com/photo-1583394838336-acd977736f90',
        'https://images.unsplash.com/photo-1480072723304-5021e468de85'
    ];

    public function handle()
    {
        $this->info('Starting database reset and seed with S3 images...');

        try {
            // Begin transaction
            DB::beginTransaction();

            // Clear existing data
            $this->info('Clearing existing data...');
            ProductGallery::truncate();
            Product::truncate();
            Merchant::truncate();
            
            // Create merchants with logos
            $this->info("\nCreating merchants with logos...");
            foreach ($this->merchantLogos as $index => $logoUrl) {
                $merchant = Merchant::factory()->create([
                    'name' => "Merchant " . ($index + 1),
                    'status' => 'ACTIVE'
                ]);

                // Download and store logo
                $logoContent = file_get_contents($logoUrl);
                $logoPath = "merchants/logos/merchant-{$merchant->id}.jpg";
                Storage::disk('public')->put($logoPath, $logoContent);
                
                $merchant->logo = $logoPath;
                $merchant->save();
                
                $this->info("✓ Created merchant: {$merchant->name}");

                // Create products for each merchant
                $this->createProductsForMerchant($merchant);
            }

            DB::commit();
            $this->info("\nDatabase reset and seed completed successfully!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during reset and seed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function createProductsForMerchant($merchant)
    {
        $categories = ProductCategory::all();
        
        foreach ($categories as $category) {
            $categoryName = strtolower($category->name);
            $images = $this->sampleImages[$categoryName] ?? $this->sampleImages['food'];

            // Create 2-3 products per category
            for ($i = 0; $i < rand(2, 3); $i++) {
                $product = Product::factory()->create([
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'status' => 'ACTIVE'
                ]);

                // Add 1-2 gallery images per product
                foreach (array_rand($images, rand(1, 2)) as $imageIndex) {
                    $imageUrl = $images[$imageIndex];
                    $imageContent = file_get_contents($imageUrl);
                    
                    $imagePath = "products/galleries/product-{$product->id}-" . ($imageIndex + 1) . ".jpg";
                    Storage::disk('public')->put($imagePath, $imageContent);

                    ProductGallery::create([
                        'product_id' => $product->id,
                        'url' => $imagePath
                    ]);
                }

                $this->info("  ✓ Created product: {$product->name}");
            }
        }
    }
}

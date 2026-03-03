<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Merchant;
use Illuminate\Database\Seeder;

class KoneksiRasaProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Koneksi Rasa merchant
        $merchant = Merchant::where('name', 'Koneksi Rasa')->first();
        
        if (!$merchant) {
            $this->command->error('Koneksi Rasa merchant not found. Please run KoneksiRasaSeeder first.');
            return;
        }

        // Get categories
        $makananCategory = ProductCategory::where('name', 'Makanan')->first();
        $minumanCategory = ProductCategory::where('name', 'Minuman')->first();
        
        // Koneksi Rasa signature dishes
        $products = [
            [
                'name' => 'Nasi Goreng Koneksi Rasa',
                'description' => 'Nasi goreng special dengan bumbu rahasia Koneksi Rasa, dilengkapi telur dan ayam suwir',
                'price' => 25000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Mie Goreng Seafood',
                'description' => 'Mie goreng dengan seafood segar (udang, cumi, ikan)',
                'price' => 35000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Ayam Bakar Taliwang',
                'description' => 'Ayam bakar dengan bumbu taliwang khas Nusa Tenggara',
                'price' => 40000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Ikan Bakar Rica',
                'description' => 'Ikan segar bakar dengan bumbu rica-rica pedas',
                'price' => 45000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Soto Betawi',
                'description' => 'Soto daging sapi khas Betawi dengan kuah susu gurih',
                'price' => 38000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Gado-gado Special',
                'description' => 'Gado-gado dengan bumbu kacang homemade dan kerupuk',
                'price' => 22000,
                'category_id' => $makananCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Es Kelapa Muda',
                'description' => 'Kelapa muda segar dengan es dan sirup',
                'price' => 15000,
                'category_id' => $minumanCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Es Teler',
                'description' => 'Es teler dengan buah segar dan susu',
                'price' => 18000,
                'category_id' => $minumanCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Jus Mangga',
                'description' => 'Jus mangga segar dengan susu kental manis',
                'price' => 16000,
                'category_id' => $minumanCategory?->id,
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Kopi Hitam',
                'description' => 'Kopi hitam robusta pilihan',
                'price' => 10000,
                'category_id' => $minumanCategory?->id,
                'status' => 'ACTIVE',
            ],
        ];

        $created = 0;
        foreach ($products as $productData) {
            Product::create([
                'merchant_id' => $merchant->id,
                'category_id' => $productData['category_id'],
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'status' => $productData['status'],
            ]);
            $created++;
        }

        $this->command->info("Successfully created {$created} products for Koneksi Rasa merchant");
    }
}

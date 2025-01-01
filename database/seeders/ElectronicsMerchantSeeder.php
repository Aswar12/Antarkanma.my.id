<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Merchant;
use App\Models\ProductGallery;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ElectronicsMerchantSeeder extends Seeder
{
    private $productsByCategory = [
        'Smartphone' => [
            ['name' => 'Samsung Galaxy S21', 'price' => [12000000, 15000000], 'description' => 'Smartphone Samsung terbaru dengan kamera 108MP'],
            ['name' => 'iPhone 13', 'price' => [14000000, 18000000], 'description' => 'iPhone dengan chip A15 Bionic'],
            ['name' => 'Xiaomi 12', 'price' => [8000000, 10000000], 'description' => 'Smartphone Xiaomi dengan Snapdragon 8 Gen 1'],
            ['name' => 'OPPO Find X5', 'price' => [11000000, 13000000], 'description' => 'OPPO flagship dengan Hasselblad camera']
        ],
        'Laptop' => [
            ['name' => 'MacBook Air M1', 'price' => [13000000, 16000000], 'description' => 'Laptop Apple dengan chip M1'],
            ['name' => 'ASUS ROG Strix', 'price' => [15000000, 20000000], 'description' => 'Laptop gaming dengan RTX 3060'],
            ['name' => 'Lenovo ThinkPad', 'price' => [12000000, 15000000], 'description' => 'Laptop bisnis dengan Intel Core i7'],
            ['name' => 'Dell XPS 13', 'price' => [16000000, 19000000], 'description' => 'Ultrabook premium dengan layar InfinityEdge']
        ],
        'Audio' => [
            ['name' => 'Sony WH-1000XM4', 'price' => [3500000, 4500000], 'description' => 'Headphone noise cancelling premium'],
            ['name' => 'AirPods Pro', 'price' => [3000000, 4000000], 'description' => 'TWS Apple dengan noise cancelling'],
            ['name' => 'JBL Flip 6', 'price' => [1500000, 2000000], 'description' => 'Speaker bluetooth tahan air'],
            ['name' => 'Samsung Galaxy Buds Pro', 'price' => [2500000, 3000000], 'description' => 'TWS Samsung dengan ANC']
        ],
        'Aksesoris' => [
            ['name' => 'Apple Watch Charger', 'price' => [300000, 500000], 'description' => 'Charger magnetic untuk Apple Watch'],
            ['name' => 'Samsung Fast Charger', 'price' => [200000, 400000], 'description' => '25W Super Fast Charging'],
            ['name' => 'Logitech MX Master 3', 'price' => [1200000, 1500000], 'description' => 'Mouse wireless premium'],
            ['name' => 'Anker PowerBank', 'price' => [500000, 800000], 'description' => 'PowerBank 20000mAh dengan PD charging']
        ]
    ];

    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::where('email', 'tech.owner@example.com')->delete();
        Merchant::where('name', 'Digital Tech Store')->delete();
        Product::truncate();
        ProductCategory::truncate();
        ProductGallery::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create merchant owner
        $owner = User::create([
            'name' => 'Digital Tech Owner',
            'email' => 'tech.owner@example.com',
            'password' => Hash::make('password123'),
            'phone_number' => '081234567890',
            'roles' => 'MERCHANT',
            'username' => 'digitaltech',
            'is_active' => true
        ]);

        // Create merchant
        $merchant = Merchant::create([
            'name' => 'Digital Tech Store',
            'owner_id' => $owner->id,
            'address' => 'Jl. Teknologi No. 123, Jakarta',
            'phone_number' => '081234567890',
            'status' => 'active',
            'description' => 'Toko elektronik terpercaya dengan produk original dan bergaransi resmi',
            'logo' => 'https://via.placeholder.com/150',
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'operating_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        ]);

        // Get all files from product-galleries directory
        $galleryFiles = Storage::disk('public')->files('product-galleries');

        if (empty($galleryFiles)) {
            $galleryFiles = [
                'product-galleries/default1.jpg',
                'product-galleries/default2.jpg',
                'product-galleries/default3.jpg',
                'product-galleries/default4.jpg',
                'product-galleries/default5.jpg'
            ];
        }

        // Create products for each category
        foreach ($this->productsByCategory as $categoryName => $products) {
            // Get or create category
            $category = ProductCategory::firstOrCreate(['name' => $categoryName]);

            // Create products
            foreach ($products as $productData) {
                $product = Product::create([
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => rand($productData['price'][0], $productData['price'][1]),
                    'status' => 'ACTIVE'
                ]);

                // Create 2-3 galleries for each product
                $numGalleries = rand(2, 3);
                for ($i = 0; $i < $numGalleries; $i++) {
                    ProductGallery::create([
                        'product_id' => $product->id,
                        'url' => $galleryFiles[array_rand($galleryFiles)]
                    ]);
                }
            }
        }
    }
}

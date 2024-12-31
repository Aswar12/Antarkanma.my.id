<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\Merchant;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductSeeder extends Seeder
{
    private $productsByCategory = [
        'Makanan' => [
            ['name' => 'Nasi Goreng Special', 'price' => [20000, 35000], 'description' => 'Nasi goreng dengan telur, ayam, udang, dan sayuran pilihan'],
            ['name' => 'Mie Goreng Special', 'price' => [20000, 35000], 'description' => 'Mie goreng dengan telur, ayam, dan sayuran segar'],
            ['name' => 'Ayam Goreng Kremes', 'price' => [25000, 40000], 'description' => 'Ayam goreng dengan bumbu kremes renyah'],
            ['name' => 'Sate Ayam Madura', 'price' => [25000, 40000], 'description' => 'Sate ayam dengan bumbu kacang khas Madura'],
            ['name' => 'Gado-gado Jakarta', 'price' => [15000, 25000], 'description' => 'Gado-gado dengan sayuran segar dan bumbu kacang'],
            ['name' => 'Soto Ayam', 'price' => [15000, 25000], 'description' => 'Soto ayam dengan kuah bening dan ayam suwir'],
            ['name' => 'Rendang Daging', 'price' => [35000, 50000], 'description' => 'Rendang daging sapi dengan bumbu khas Padang'],
            ['name' => 'Nasi Uduk', 'price' => [15000, 25000], 'description' => 'Nasi uduk dengan lauk lengkap'],
            ['name' => 'Bakso Malang', 'price' => [15000, 30000], 'description' => 'Bakso dengan pentol besar dan mie'],
            ['name' => 'Sop Buntut', 'price' => [40000, 60000], 'description' => 'Sop buntut sapi dengan kuah bening']
        ],
        'Minuman' => [
            ['name' => 'Es Teh Manis', 'price' => [5000, 10000], 'description' => 'Teh manis dingin segar'],
            ['name' => 'Es Jeruk Peras', 'price' => [7000, 12000], 'description' => 'Jeruk peras segar dengan es'],
            ['name' => 'Jus Alpukat', 'price' => [12000, 18000], 'description' => 'Jus alpukat segar dengan susu'],
            ['name' => 'Es Campur', 'price' => [10000, 15000], 'description' => 'Es campur dengan aneka topping'],
            ['name' => 'Kopi Hitam', 'price' => [8000, 15000], 'description' => 'Kopi hitam original'],
            ['name' => 'Es Cincau', 'price' => [8000, 12000], 'description' => 'Es cincau hijau segar'],
            ['name' => 'Jus Mangga', 'price' => [10000, 15000], 'description' => 'Jus mangga segar'],
            ['name' => 'Es Kelapa Muda', 'price' => [10000, 15000], 'description' => 'Es kelapa muda segar']
        ],
        'Snack' => [
            ['name' => 'Kentang Goreng', 'price' => [10000, 20000], 'description' => 'Kentang goreng crispy'],
            ['name' => 'Pisang Goreng', 'price' => [8000, 15000], 'description' => 'Pisang goreng crispy'],
            ['name' => 'Cireng', 'price' => [5000, 10000], 'description' => 'Cireng dengan bumbu rujak'],
            ['name' => 'Tahu Crispy', 'price' => [8000, 15000], 'description' => 'Tahu crispy dengan sambal kecap'],
            ['name' => 'Tempe Mendoan', 'price' => [8000, 15000], 'description' => 'Tempe mendoan dengan tepung crispy'],
            ['name' => 'Risoles', 'price' => [8000, 15000], 'description' => 'Risoles dengan isian sayuran'],
            ['name' => 'Bakwan Sayur', 'price' => [8000, 15000], 'description' => 'Bakwan dengan sayuran segar']
        ],
        'Buah & Sayur' => [
            ['name' => 'Apel Fuji', 'price' => [25000, 40000], 'description' => 'Apel fuji segar per kg'],
            ['name' => 'Jeruk Mandarin', 'price' => [20000, 35000], 'description' => 'Jeruk mandarin manis per kg'],
            ['name' => 'Bayam', 'price' => [5000, 8000], 'description' => 'Bayam segar per ikat'],
            ['name' => 'Wortel', 'price' => [12000, 18000], 'description' => 'Wortel segar per kg'],
            ['name' => 'Brokoli', 'price' => [15000, 25000], 'description' => 'Brokoli segar per kg'],
            ['name' => 'Kangkung', 'price' => [5000, 8000], 'description' => 'Kangkung segar per ikat'],
            ['name' => 'Pisang', 'price' => [15000, 25000], 'description' => 'Pisang segar per sisir']
        ],
        'Daging & Ikan' => [
            ['name' => 'Daging Sapi Segar', 'price' => [120000, 150000], 'description' => 'Daging sapi segar per kg'],
            ['name' => 'Ayam Potong', 'price' => [35000, 45000], 'description' => 'Ayam potong segar per ekor'],
            ['name' => 'Ikan Salmon', 'price' => [150000, 200000], 'description' => 'Ikan salmon segar per kg'],
            ['name' => 'Udang Segar', 'price' => [80000, 120000], 'description' => 'Udang segar per kg'],
            ['name' => 'Ikan Tuna', 'price' => [90000, 130000], 'description' => 'Ikan tuna segar per kg'],
            ['name' => 'Daging Kambing', 'price' => [100000, 140000], 'description' => 'Daging kambing segar per kg']
        ],
        'Bumbu Dapur' => [
            ['name' => 'Bawang Putih', 'price' => [30000, 40000], 'description' => 'Bawang putih segar per kg'],
            ['name' => 'Bawang Merah', 'price' => [35000, 45000], 'description' => 'Bawang merah segar per kg'],
            ['name' => 'Cabai Merah', 'price' => [40000, 60000], 'description' => 'Cabai merah segar per kg'],
            ['name' => 'Jahe', 'price' => [25000, 35000], 'description' => 'Jahe segar per kg'],
            ['name' => 'Kunyit', 'price' => [20000, 30000], 'description' => 'Kunyit segar per kg'],
            ['name' => 'Lengkuas', 'price' => [15000, 25000], 'description' => 'Lengkuas segar per kg']
        ],
        'Bahan Pokok' => [
            ['name' => 'Beras Premium', 'price' => [65000, 80000], 'description' => 'Beras premium per 5kg'],
            ['name' => 'Minyak Goreng', 'price' => [20000, 30000], 'description' => 'Minyak goreng per 2L'],
            ['name' => 'Gula Pasir', 'price' => [15000, 20000], 'description' => 'Gula pasir per kg'],
            ['name' => 'Tepung Terigu', 'price' => [12000, 18000], 'description' => 'Tepung terigu per kg'],
            ['name' => 'Telur Ayam', 'price' => [25000, 35000], 'description' => 'Telur ayam per kg'],
            ['name' => 'Kecap Manis', 'price' => [10000, 15000], 'description' => 'Kecap manis per botol']
        ],
        'Frozen Food' => [
            ['name' => 'Nugget Ayam', 'price' => [35000, 45000], 'description' => 'Nugget ayam beku 500g'],
            ['name' => 'Sosis Sapi', 'price' => [30000, 40000], 'description' => 'Sosis sapi beku 300g'],
            ['name' => 'Bakso Sapi', 'price' => [40000, 50000], 'description' => 'Bakso sapi beku 500g'],
            ['name' => 'Dimsum', 'price' => [35000, 45000], 'description' => 'Dimsum beku 300g'],
            ['name' => 'Udang Beku', 'price' => [60000, 80000], 'description' => 'Udang beku 500g'],
            ['name' => 'Kentang Goreng Beku', 'price' => [25000, 35000], 'description' => 'Kentang goreng beku 500g']
        ]
    ];

    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        ProductCategory::truncate();
        ProductGallery::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ensure we have merchants
        $merchantCount = \App\Models\Merchant::count();
        if ($merchantCount == 0) {
            \App\Models\Merchant::factory(5)->create();
        }

        // Get merchant with owner_id 22
        $merchant = Merchant::where('owner_id', 22)->first();

        if (!$merchant) {
            throw new \Exception('Merchant with owner_id 22 not found. Please ensure the merchant exists before running this seeder.');
        }

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

        // Create each category and its products
        foreach ($this->productsByCategory as $categoryName => $products) {
            // Create category
            $category = ProductCategory::create(['name' => $categoryName]);

            // Create all products for this category
            foreach ($products as $productData) {
                // Create product
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
                for ($j = 0; $j < $numGalleries; $j++) {
                    ProductGallery::create([
                        'product_id' => $product->id,
                        'url' => $galleryFiles[array_rand($galleryFiles)]
                    ]);
                }
            }
        }
    }
}

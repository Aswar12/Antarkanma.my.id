<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\ProductReview;
use App\Models\ProductCategory;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Additional50ProductsSeeder extends Seeder
{
    private $additionalProducts = [
        'Makanan' => [
            ['name' => 'Ayam Bakar Taliwang', 'price' => [35000, 45000], 'description' => 'Ayam bakar dengan bumbu khas Taliwang'],
            ['name' => 'Ikan Bakar Rica', 'price' => [40000, 55000], 'description' => 'Ikan bakar dengan bumbu rica-rica pedas'],
            ['name' => 'Cumi Goreng Tepung', 'price' => [30000, 45000], 'description' => 'Cumi segar digoreng dengan tepung crispy'],
            ['name' => 'Udang Goreng Mentega', 'price' => [45000, 60000], 'description' => 'Udang dimasak dengan saus mentega special'],
            ['name' => 'Nasi Kuning Komplit', 'price' => [25000, 35000], 'description' => 'Nasi kuning dengan lauk lengkap'],
            ['name' => 'Gado-gado Special', 'price' => [20000, 30000], 'description' => 'Gado-gado dengan bumbu kacang homemade'],
            ['name' => 'Sop Buntut', 'price' => [50000, 75000], 'description' => 'Sop buntut sapi dengan kuah bening'],
            ['name' => 'Soto Betawi', 'price' => [35000, 45000], 'description' => 'Soto daging sapi khas Betawi'],
            ['name' => 'Mie Goreng Seafood', 'price' => [35000, 45000], 'description' => 'Mie goreng dengan aneka seafood segar'],
            ['name' => 'Nasi Campur Bali', 'price' => [30000, 40000], 'description' => 'Nasi campur dengan lauk khas Bali']
        ],
        'Minuman' => [
            ['name' => 'Es Kelapa Muda', 'price' => [15000, 20000], 'description' => 'Kelapa muda segar dengan es'],
            ['name' => 'Es Cincau', 'price' => [10000, 15000], 'description' => 'Es cincau hitam dengan susu'],
            ['name' => 'Jus Mangga', 'price' => [15000, 20000], 'description' => 'Jus mangga segar dengan susu'],
            ['name' => 'Es Teler', 'price' => [20000, 25000], 'description' => 'Es teler dengan buah segar'],
            ['name' => 'Smoothie Buah Naga', 'price' => [20000, 25000], 'description' => 'Smoothie buah naga dengan yogurt']
        ],
        'Snack' => [
            ['name' => 'Pisang Goreng Crispy', 'price' => [15000, 20000], 'description' => 'Pisang goreng dengan topping crispy'],
            ['name' => 'Kentang Goreng', 'price' => [15000, 25000], 'description' => 'Kentang goreng crispy dengan saus'],
            ['name' => 'Roti Bakar Special', 'price' => [20000, 30000], 'description' => 'Roti bakar dengan berbagai topping'],
            ['name' => 'Dimsum Aneka Rasa', 'price' => [25000, 35000], 'description' => 'Dimsum dengan berbagai isian'],
            ['name' => 'Martabak Mini', 'price' => [25000, 35000], 'description' => 'Martabak mini dengan berbagai rasa']
        ],
        'Pakaian' => [
            ['name' => 'Kemeja Casual', 'price' => [150000, 250000], 'description' => 'Kemeja casual modern'],
            ['name' => 'Celana Jeans', 'price' => [200000, 350000], 'description' => 'Celana jeans premium'],
            ['name' => 'Kaos Polos', 'price' => [80000, 120000], 'description' => 'Kaos polos berbagai warna'],
            ['name' => 'Jaket Denim', 'price' => [250000, 400000], 'description' => 'Jaket denim stylish'],
            ['name' => 'Rok Midi', 'price' => [150000, 250000], 'description' => 'Rok midi elegant']
        ],
        'Aksesoris' => [
            ['name' => 'Tas Selempang', 'price' => [100000, 200000], 'description' => 'Tas selempang trendy'],
            ['name' => 'Dompet Kulit', 'price' => [150000, 250000], 'description' => 'Dompet kulit asli'],
            ['name' => 'Topi Baseball', 'price' => [50000, 100000], 'description' => 'Topi baseball casual'],
            ['name' => 'Kacamata Fashion', 'price' => [100000, 200000], 'description' => 'Kacamata fashion modern'],
            ['name' => 'Jam Tangan Casual', 'price' => [200000, 400000], 'description' => 'Jam tangan casual elegant']
        ],
        'Elektronik' => [
            ['name' => 'Power Bank 10000mAh', 'price' => [200000, 300000], 'description' => 'Power bank kapasitas besar'],
            ['name' => 'Speaker Bluetooth', 'price' => [150000, 300000], 'description' => 'Speaker bluetooth portable'],
            ['name' => 'Earphone Wireless', 'price' => [100000, 200000], 'description' => 'Earphone wireless dengan bass'],
            ['name' => 'Charger HP Fast Charging', 'price' => [100000, 200000], 'description' => 'Charger HP dengan fast charging'],
            ['name' => 'Mouse Wireless', 'price' => [80000, 150000], 'description' => 'Mouse wireless ergonomis']
        ],
        'Peralatan Rumah' => [
            ['name' => 'Panci Set', 'price' => [200000, 400000], 'description' => 'Set panci anti lengket'],
            ['name' => 'Blender', 'price' => [300000, 500000], 'description' => 'Blender multifungsi'],
            ['name' => 'Wajan Anti Lengket', 'price' => [150000, 250000], 'description' => 'Wajan anti lengket premium'],
            ['name' => 'Talenan Kayu', 'price' => [50000, 100000], 'description' => 'Talenan dari kayu jati'],
            ['name' => 'Set Pisau Dapur', 'price' => [200000, 400000], 'description' => 'Set pisau dapur stainless']
        ],
        'Kesehatan' => [
            ['name' => 'Masker KN95', 'price' => [10000, 20000], 'description' => 'Masker KN95 5 lapis'],
            ['name' => 'Hand Sanitizer', 'price' => [25000, 40000], 'description' => 'Hand sanitizer antibakteri'],
            ['name' => 'Vitamin C', 'price' => [50000, 100000], 'description' => 'Vitamin C 1000mg'],
            ['name' => 'Minyak Kayu Putih', 'price' => [30000, 50000], 'description' => 'Minyak kayu putih asli'],
            ['name' => 'Kotak P3K', 'price' => [100000, 200000], 'description' => 'Kotak P3K lengkap']
        ],
        'Alat Tulis' => [
            ['name' => 'Set Pulpen Gel', 'price' => [50000, 100000], 'description' => 'Set pulpen gel warna-warni'],
            ['name' => 'Buku Catatan A5', 'price' => [30000, 50000], 'description' => 'Buku catatan hardcover A5'],
            ['name' => 'Pensil Mekanik', 'price' => [20000, 40000], 'description' => 'Pensil mekanik 0.5mm'],
            ['name' => 'Penghapus', 'price' => [5000, 10000], 'description' => 'Penghapus karet lembut'],
            ['name' => 'Spidol Marker', 'price' => [15000, 30000], 'description' => 'Spidol marker permanen']
        ]
    ];

    private $reviewComments = [
        'Produk sangat bagus dan berkualitas!',
        'Harga terjangkau untuk kualitas sebagus ini',
        'Pengiriman cepat dan produk sesuai deskripsi',
        'Recommended seller, akan belanja lagi',
        'Puas dengan pelayanannya',
        'Kualitas produk melebihi ekspektasi',
        'Harga bersaing dengan toko lain',
        'Produk original dan bergaransi',
        'Seller responsif dan helpful',
        'Pelayanan memuaskan'
    ];

    public function run()
    {
        // Get existing merchants
        $merchants = Merchant::all();
        if ($merchants->isEmpty()) {
            throw new \Exception('No merchants found. Please run MerchantLocationCompleteSeeder first.');
        }

        // Get gallery images
        $galleryFiles = Storage::disk('public')->files('product-galleries');
        if (empty($galleryFiles)) {
            throw new \Exception('No gallery images found in storage/app/public/product-galleries');
        }

        // Get users for reviews
        $reviewers = User::where('roles', 'user')->limit(5)->get();
        if ($reviewers->isEmpty()) {
            $reviewers = User::factory(5)->create(['roles' => 'user']);
        }

        // Create categories and products
        foreach ($this->additionalProducts as $categoryName => $products) {
            $category = ProductCategory::firstOrCreate(['name' => $categoryName]);

            foreach ($products as $productData) {
                // Randomly assign to a merchant
                $merchant = $merchants->random();

                $product = Product::create([
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => rand($productData['price'][0], $productData['price'][1]),
                    'status' => 'active'
                ]);

                // Create 3 galleries for each product
                $productGalleryFiles = array_rand(array_flip($galleryFiles), 3);
                foreach ($productGalleryFiles as $file) {
                    ProductGallery::create([
                        'product_id' => $product->id,
                        'url' => $file
                    ]);
                }

                // Create 2-4 reviews for each product
                $numReviews = rand(2, 4);
                $reviewUsers = $reviewers->random($numReviews);
                foreach ($reviewUsers as $reviewer) {
                    ProductReview::create([
                        'product_id' => $product->id,
                        'user_id' => $reviewer->id,
                        'rating' => rand(4, 5),
                        'comment' => $this->reviewComments[array_rand($this->reviewComments)]
                    ]);
                }
            }
        }
    }
}

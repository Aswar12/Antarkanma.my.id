<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\UserLocation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MerchantLocationCompleteSeeder extends Seeder
{
    private $reviewComments = [
        'Produk sangat bagus dan berkualitas!',
        'Pelayanan ramah dan cepat',
        'Harga terjangkau untuk kualitas sebagus ini',
        'Recommended seller, akan belanja lagi',
        'Pengiriman cepat dan produk sesuai deskripsi',
        'Puas dengan pelayanannya',
        'Kualitas produk melebihi ekspektasi',
        'Harga bersaing dengan toko lain',
        'Produk original dan bergaransi',
        'Seller responsif dan helpful'
    ];

    private $merchantData = [
        // Segeri Area - Pusat Kota Segeri (Updated to be on land)
        [
            'name' => 'Toko Elektronik Segeri',
            'address' => 'Jl. Poros Segeri No. 123',
            'district' => 'Segeri',
            'latitude' => -4.632991,  // Updated to be on land
            'longitude' => 119.585339, // Updated to be on land
            'products' => [
                [
                    'name' => 'Kipas Angin 16 inch',
                    'price' => 250000,
                    'description' => 'Kipas angin berkualitas dengan 3 kecepatan',
                    'category' => 'Elektronik'
                ],
                [
                    'name' => 'Rice Cooker 1.8L',
                    'price' => 350000,
                    'description' => 'Rice cooker dengan teknologi terbaru',
                    'category' => 'Elektronik'
                ]
            ]
        ],
        [
            'name' => 'Warung Makan Segeri Raya',
            'address' => 'Jl. Pasar Segeri No. 45',
            'district' => 'Segeri',
            'latitude' => -4.633500,  // Slightly offset from center
            'longitude' => 119.586000,
            'products' => [
                [
                    'name' => 'Nasi Goreng Seafood',
                    'price' => 25000,
                    'description' => 'Nasi goreng dengan udang dan cumi segar',
                    'category' => 'Makanan'
                ],
                [
                    'name' => 'Soto Ayam',
                    'price' => 15000,
                    'description' => 'Soto ayam dengan kuah bening',
                    'category' => 'Makanan'
                ]
            ]
        ],
        // Mandalle Area - Sekitar Mandalle (Updated to be on land)
        [
            'name' => 'Toko Bangunan Mandalle',
            'address' => 'Jl. Poros Mandalle No. 78',
            'district' => 'Mandalle',
            'latitude' => -4.615000,  // North of Segeri
            'longitude' => 119.585000,
            'products' => [
                [
                    'name' => 'Semen 40kg',
                    'price' => 75000,
                    'description' => 'Semen berkualitas tinggi',
                    'category' => 'Bahan Bangunan'
                ],
                [
                    'name' => 'Cat Tembok 5kg',
                    'price' => 120000,
                    'description' => 'Cat tembok tahan lama',
                    'category' => 'Bahan Bangunan'
                ]
            ]
        ],
        [
            'name' => 'Apotek Mandalle Sehat',
            'address' => 'Jl. Mandalle Raya No. 90',
            'district' => 'Mandalle',
            'latitude' => -4.616000,  // Slightly offset
            'longitude' => 119.586000,
            'products' => [
                [
                    'name' => 'Paracetamol Strip',
                    'price' => 12000,
                    'description' => 'Obat penurun panas',
                    'category' => 'Obat & Kesehatan'
                ],
                [
                    'name' => 'Vitamin C',
                    'price' => 25000,
                    'description' => 'Vitamin C 500mg',
                    'category' => 'Obat & Kesehatan'
                ]
            ]
        ],
        // Marang Area - Sekitar Marang (Updated to be on land)
        [
            'name' => 'Toko Sembako Marang',
            'address' => 'Jl. Poros Marang No. 56',
            'district' => 'Marang',
            'latitude' => -4.645000,  // Between Segeri and Mandalle
            'longitude' => 119.584000,
            'products' => [
                [
                    'name' => 'Beras 5kg',
                    'price' => 70000,
                    'description' => 'Beras premium berkualitas',
                    'category' => 'Sembako'
                ],
                [
                    'name' => 'Minyak Goreng 2L',
                    'price' => 35000,
                    'description' => 'Minyak goreng bening',
                    'category' => 'Sembako'
                ]
            ]
        ],
        [
            'name' => 'Warung Bakso Marang',
            'address' => 'Jl. Marang Indah No. 34',
            'district' => 'Marang',
            'latitude' => -4.646000,  // Slightly offset
            'longitude' => 119.585000,
            'products' => [
                [
                    'name' => 'Bakso Spesial',
                    'price' => 20000,
                    'description' => 'Bakso dengan daging sapi pilihan',
                    'category' => 'Makanan'
                ],
                [
                    'name' => 'Mie Ayam',
                    'price' => 15000,
                    'description' => 'Mie ayam dengan topping melimpah',
                    'category' => 'Makanan'
                ]
            ]
        ]
    ];

    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Delete merchants and their related data
        $merchantUserIds = Merchant::pluck('owner_id');
        User::whereIn('id', $merchantUserIds)->delete();
        Merchant::truncate();
        UserLocation::whereIn('user_id', $merchantUserIds)->delete();
        
        // Delete products and their related data
        Product::truncate();
        ProductGallery::truncate();
        ProductReview::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get profile photos for merchant logos
        $profilePhotos = Storage::disk('public')->files('profile-photos');
        if (empty($profilePhotos)) {
            // Create the directory if it doesn't exist
            Storage::disk('public')->makeDirectory('profile-photos');
            // Copy a default image if no profile photos exist
            Storage::disk('public')->copy('images/default-logo.png', 'profile-photos/default-logo.png');
            $profilePhotos = ['profile-photos/default-logo.png'];
        }

        // Get gallery images
        $galleryFiles = Storage::disk('public')->files('product-galleries');
        if (empty($galleryFiles)) {
            throw new \Exception('No gallery images found in storage/app/public/product-galleries');
        }

        // Create some users for reviews
        $reviewers = User::factory(5)->create(['roles' => 'user']);

        foreach ($this->merchantData as $index => $data) {
            // Create user for merchant with unique email
            $user = User::create([
                'name' => 'Owner ' . $data['name'],
                'email' => strtolower(str_replace([' ', '@example.com'], '', $data['name'])) . rand(1000, 9999) . '@example.com',
                'password' => bcrypt('password'),
                'roles' => 'merchant',
                'phone_number' => '08' . rand(1000000000, 9999999999),
                'profile_photo_path' => $profilePhotos[$index % count($profilePhotos)],
            ]);

            // Create merchant
            $merchant = Merchant::create([
                'owner_id' => $user->id,
                'name' => $data['name'],
                'address' => $data['address'],
                'phone_number' => $user->phone_number,
                'status' => 'active',
                'description' => 'Merchant di wilayah ' . $data['district'],
                'opening_time' => Carbon::createFromTime(8, 0, 0),
                'closing_time' => Carbon::createFromTime(20, 0, 0),
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'logo' => $user->profile_photo_path,
            ]);

            // Create user location
            UserLocation::create([
                'user_id' => $user->id,
                'address' => $data['address'],
                'district' => $data['district'],
                'city' => 'Pangkep',
                'postal_code' => '90655',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address_type' => 'Toko',
                'phone_number' => $user->phone_number,
                'is_default' => true,
                'is_active' => true,
                'country' => 'Indonesia',
            ]);

            // Create products for merchant
            foreach ($data['products'] as $productData) {
                // Get or create category
                $category = ProductCategory::firstOrCreate([
                    'name' => $productData['category']
                ]);

                // Create product
                $product = Product::create([
                    'name' => $productData['name'],
                    'price' => $productData['price'],
                    'description' => $productData['description'],
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
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

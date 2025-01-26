<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\ProductReview;
use App\Models\ProductCategory;
use App\Models\Merchant;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewProductWithGalleryAndReviewSeeder extends Seeder
{
    private $productsByCategory = [
        'Makanan' => [
            ['name' => 'Nasi Goreng Special', 'price' => [20000, 35000], 'description' => 'Nasi goreng dengan telur, ayam, udang, dan sayuran pilihan'],
            ['name' => 'Mie Goreng Special', 'price' => [20000, 35000], 'description' => 'Mie goreng dengan telur, ayam, dan sayuran segar'],
            ['name' => 'Ayam Goreng Kremes', 'price' => [25000, 40000], 'description' => 'Ayam goreng dengan bumbu kremes renyah'],
            ['name' => 'Sate Ayam Madura', 'price' => [25000, 40000], 'description' => 'Sate ayam dengan bumbu kacang khas Madura'],
            ['name' => 'Rendang Daging', 'price' => [35000, 50000], 'description' => 'Rendang daging sapi dengan bumbu khas Padang'],
        ],
        'Minuman' => [
            ['name' => 'Es Teh Manis', 'price' => [5000, 10000], 'description' => 'Teh manis dingin segar'],
            ['name' => 'Es Jeruk Peras', 'price' => [7000, 12000], 'description' => 'Jeruk peras segar dengan es'],
            ['name' => 'Jus Alpukat', 'price' => [12000, 18000], 'description' => 'Jus alpukat segar dengan susu'],
            ['name' => 'Es Kopi Susu', 'price' => [15000, 25000], 'description' => 'Es kopi susu gula aren'],
            ['name' => 'Matcha Latte', 'price' => [18000, 28000], 'description' => 'Matcha latte dengan susu segar'],
        ],
        'Electronics' => [
            ['name' => 'Smart LED TV 55"', 'price' => [8000000, 12000000], 'description' => 'Ultra HD 4K Smart LED TV with HDR'],
            ['name' => 'Wireless Headphones', 'price' => [800000, 1500000], 'description' => 'Noise-canceling wireless headphones'],
            ['name' => 'Smartphone Pro', 'price' => [5000000, 8000000], 'description' => '5G smartphone with pro camera system'],
            ['name' => 'Gaming Laptop', 'price' => [15000000, 25000000], 'description' => 'High-performance gaming laptop'],
        ],
        'Fashion' => [
            ['name' => 'Leather Jacket', 'price' => [500000, 1200000], 'description' => 'Premium leather jacket'],
            ['name' => 'Designer Sunglasses', 'price' => [300000, 800000], 'description' => 'UV protection designer sunglasses'],
            ['name' => 'Running Shoes', 'price' => [800000, 1500000], 'description' => 'Professional running shoes'],
            ['name' => 'Casual Watch', 'price' => [400000, 900000], 'description' => 'Elegant casual watch'],
        ],
        'Home & Living' => [
            ['name' => 'Coffee Maker', 'price' => [500000, 1500000], 'description' => 'Automatic coffee maker machine'],
            ['name' => 'Air Purifier', 'price' => [1000000, 2000000], 'description' => 'HEPA filter air purifier'],
            ['name' => 'Bedding Set', 'price' => [300000, 800000], 'description' => 'Premium cotton bedding set'],
            ['name' => 'Table Lamp', 'price' => [150000, 400000], 'description' => 'Modern LED table lamp'],
        ],
        'Sports & Outdoors' => [
            ['name' => 'Yoga Mat', 'price' => [100000, 300000], 'description' => 'Non-slip yoga mat'],
            ['name' => 'Camping Tent', 'price' => [500000, 1500000], 'description' => '4-person camping tent'],
            ['name' => 'Bicycle', 'price' => [2000000, 5000000], 'description' => 'Mountain bike with 21 speeds'],
            ['name' => 'Fitness Tracker', 'price' => [400000, 1000000], 'description' => 'Smart fitness tracking watch'],
        ]
    ];

    private $reviewComments = [
        'Excellent product! Exactly what I was looking for.',
        'Great quality for the price. Highly recommended!',
        'Good product but delivery took longer than expected.',
        'Amazing value for money. Will buy again.',
        'Product matches the description perfectly.',
        'Very satisfied with my purchase.',
        'Better than expected! Really happy with it.',
        'Good quality but a bit pricey.',
        'Decent product, fast shipping.',
        'Outstanding quality and service!'
    ];

    private $merchantLocations = [
        [
            'district' => 'Segeri',
            'latitude' => -4.632991,
            'longitude' => 119.585339,
            'address' => 'Jl. Poros Segeri No. 22'
        ],
        [
            'district' => 'Mandalle',
            'latitude' => -4.615000,
            'longitude' => 119.585000,
            'address' => 'Jl. Poros Mandalle No. 22'
        ],
        [
            'district' => 'Marang',
            'latitude' => -4.645000,
            'longitude' => 119.584000,
            'address' => 'Jl. Poros Marang No. 22'
        ]
    ];

    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        ProductCategory::truncate();
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

        // Create merchant for user 22 if not exists
        $user22 = User::find(22);
        if (!$user22) {
            $user22 = User::factory()->create([
                'id' => 22,
                'roles' => 'merchant',
                'profile_photo_path' => $profilePhotos[0]
            ]);
        } else {
            $user22->update([
                'roles' => 'merchant',
                'profile_photo_path' => $profilePhotos[0]
            ]);
        }

        $merchant22 = Merchant::where('owner_id', 22)->first();
        $location22 = $this->merchantLocations[0]; // Segeri location
        if (!$merchant22) {
            $merchant22 = Merchant::create([
                'owner_id' => 22,
                'name' => 'Merchant 22 Store',
                'address' => $location22['address'],
                'phone_number' => '08123456789',
                'status' => 'active',
                'description' => 'A multi-category store offering various products',
                'opening_time' => Carbon::createFromTime(8, 0, 0),
                'closing_time' => Carbon::createFromTime(22, 0, 0),
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'logo' => $user22->profile_photo_path
            ]);

            // Create location for merchant 22
            UserLocation::create([
                'user_id' => 22,
                'address' => $location22['address'],
                'district' => $location22['district'],
                'city' => 'Pangkep',
                'postal_code' => '90655',
                'latitude' => $location22['latitude'],
                'longitude' => $location22['longitude'],
                'address_type' => 'Toko',
                'phone_number' => '08123456789',
                'is_default' => true,
                'is_active' => true,
                'country' => 'Indonesia',
            ]);
        }

        // Create other merchants
        $otherMerchants = [];
        foreach (array_slice($this->merchantLocations, 1) as $index => $location) {
            $user = User::factory()->create([
                'roles' => 'merchant',
                'profile_photo_path' => $profilePhotos[($index + 1) % count($profilePhotos)]
            ]);

            $merchant = Merchant::create([
                'owner_id' => $user->id,
                'name' => "Merchant Store " . ($index + 1),
                'address' => $location['address'],
                'phone_number' => '08' . rand(1000000000, 9999999999),
                'status' => 'active',
                'description' => 'Merchant di wilayah ' . $location['district'],
                'opening_time' => Carbon::createFromTime(8, 0, 0),
                'closing_time' => Carbon::createFromTime(20, 0, 0),
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'logo' => $user->profile_photo_path
            ]);

            UserLocation::create([
                'user_id' => $user->id,
                'address' => $location['address'],
                'district' => $location['district'],
                'city' => 'Pangkep',
                'postal_code' => '90655',
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'address_type' => 'Toko',
                'phone_number' => $merchant->phone_number,
                'is_default' => true,
                'is_active' => true,
                'country' => 'Indonesia',
            ]);

            $otherMerchants[] = $merchant;
        }

        // Get all gallery images
        $galleryFiles = Storage::disk('public')->files('product-galleries');
        if (empty($galleryFiles)) {
            throw new \Exception('No gallery images found in storage/app/public/product-galleries');
        }

        // Get some users for reviews
        $users = User::where('id', '!=', 22)->limit(5)->get();
        if ($users->count() < 5) {
            $needed = 5 - $users->count();
            $newUsers = User::factory($needed)->create();
            $users = $users->concat($newUsers);
        }

        // Create categories and products
        foreach ($this->productsByCategory as $categoryName => $products) {
            $category = ProductCategory::create([
                'name' => $categoryName
            ]);

            foreach ($products as $index => $productData) {
                // Assign to merchant 22 for first product in each category, random for others
                $merchant = ($index === 0) ? $merchant22 : $otherMerchants[array_rand($otherMerchants)];

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
                $reviewUsers = $users->random($numReviews);
                foreach ($reviewUsers as $user) {
                    ProductReview::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'rating' => rand(4, 5),
                        'comment' => $this->reviewComments[array_rand($this->reviewComments)]
                    ]);
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use App\Models\ProductReview;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestAccountsSeeder extends Seeder
{
    private $products = [
        'Makanan' => [
            [
                'name' => 'Nasi Goreng Special',
                'price' => 25000,
                'description' => 'Nasi goreng dengan telur, ayam, dan sayuran pilihan',
                'images' => [
                    'pexels-kelvinocta16-7190355.jpg',
                    'pexels-kelvinocta16-7190356.jpg',
                    'pexels-kelvinocta16-7190359.jpg'
                ]
            ],
            [
                'name' => 'Mie Goreng Special',
                'price' => 23000,
                'description' => 'Mie goreng dengan telur dan sayuran segar',
                'images' => [
                    'pexels-meggy-kadam-aryanto-3797063-7429247.jpg',
                    'pexels-meggy-kadam-aryanto-3797063-9620137.jpg',
                    'pexels-meggy-kadam-aryanto-3797063-11346027.jpg'
                ]
            ],
            [
                'name' => 'Ayam Goreng Kremes',
                'price' => 30000,
                'description' => 'Ayam goreng dengan bumbu kremes renyah',
                'images' => [
                    'pexels-ffawdyy-13294514.jpg',
                    'pexels-ffawdyy-13294534.jpg',
                    'pexels-ffawdyy-13294536.jpg'
                ]
            ]
        ],
        'Minuman' => [
            [
                'name' => 'Es Teh Manis',
                'price' => 5000,
                'description' => 'Teh manis dingin segar',
                'images' => [
                    'pexels-pixabay-372851.jpg',
                    'pexels-polina-tankilevitch-4110012.jpg',
                    'pexels-pragyanbezbo-2010701.jpg'
                ]
            ],
            [
                'name' => 'Es Jeruk',
                'price' => 7000,
                'description' => 'Jeruk peras segar dengan es',
                'images' => [
                    'pexels-tijana-drndarski-449691-3338681.jpg',
                    'pexels-tijana-drndarski-449691-3656119.jpg',
                    'pexels-pixabay-279906.jpg'
                ]
            ]
        ]
    ];

    private $reviewComments = [
        'Sangat enak dan pelayanan ramah!',
        'Makanannya fresh dan cepat sampai',
        'Recommended banget, harga terjangkau',
        'Rasa mantap, porsi pas',
        'Puas dengan pelayanannya'
    ];

    public function run()
    {
        // Create Merchant Account
        $merchantUser = User::create([
            'name' => 'Test Merchant',
            'email' => 'merchant@test.com', 
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567891',
            'roles' => 'MERCHANT',
            'username' => 'testmerchant',
            'is_active' => true
        ]);

        // Create merchant profile
        $merchant = Merchant::create([
            'owner_id' => $merchantUser->id,
            'name' => 'Test Merchant Store',
            'address' => 'Jl. Poros Segeri No. 123',
            'phone_number' => '081234567891',
            'status' => 'active',
            'description' => 'Warung makan dan minuman dengan berbagai menu special',
            'opening_time' => Carbon::createFromTime(8, 0, 0),
            'closing_time' => Carbon::createFromTime(22, 0, 0),
            'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            'logo' => 'product-galleries/pexels-kelvinocta16-7190355.jpg'
        ]);

        // Create merchant location
        UserLocation::create([
            'user_id' => $merchantUser->id,
            'address' => 'Jl. Poros Segeri No. 123',
            'district' => 'Segeri',
            'city' => 'Pangkep',
            'postal_code' => '90655',
            'latitude' => -4.632991,
            'longitude' => 119.585339,
            'address_type' => 'Toko',
            'phone_number' => '081234567891',
            'is_default' => true,
            'is_active' => true,
            'country' => 'Indonesia'
        ]);

        // Create Courier Account
        User::create([
            'name' => 'Test Courier',
            'email' => 'courier@test.com',
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567892',
            'roles' => 'COURIER',
            'username' => 'testcourier',
            'is_active' => true
        ]);

        // Create Regular User Account for reviews
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567893',
            'roles' => 'USER',
            'username' => 'testuser',
            'is_active' => true
        ]);

        // Create categories and products
        foreach ($this->products as $categoryName => $products) {
            $category = ProductCategory::create([
                'name' => $categoryName
            ]);

            foreach ($products as $productData) {
                $product = Product::create([
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'status' => 'active'
                ]);

                // Create galleries for each product using specified images
                foreach ($productData['images'] as $image) {
                    ProductGallery::create([
                        'product_id' => $product->id,
                        'url' => 'product-galleries/' . $image
                    ]);
                }

                // Create 2 reviews for each product
                for ($i = 0; $i < 2; $i++) {
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

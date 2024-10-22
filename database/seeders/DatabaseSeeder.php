<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\ProductGallery;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CourierSeeder::class,
            LoyaltyPointSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            UserSeeder::class,
            ProductCategorySeeder::class,
            ProductGallerySeeder::class,
            OrderItemSeeder::class,
            MerchantSeeder::class,
            TransactionSeeder::class,
            TransactionItemSeeder::class,
            DeliverySeeder::class,
            UserLocationSeeder::class,
            ProductReviewSeeder::class,
        ]);
    }
}

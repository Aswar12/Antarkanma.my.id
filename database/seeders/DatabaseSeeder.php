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
            UserSeeder::class,
            MerchantLocationCompleteSeeder::class, // New seeder for merchants with locations
            CourierSeeder::class,
            LoyaltyPointSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            TransactionSeeder::class,
            TransactionItemSeeder::class,
            DeliverySeeder::class,
        ]);
    }
}

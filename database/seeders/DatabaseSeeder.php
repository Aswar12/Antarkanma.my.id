<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\ProductGallery;
use App\Models\User;
use Database\Seeders\TestMerchantSeeder;
use Database\Seeders\KoneksiRasaSeeder;
use Database\Seeders\AntarKanMaCourierSeeder;
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
            Additional50ProductsSeeder::class, // Add products including Penghapus
            CourierSeeder::class,
            LoyaltyPointSeeder::class,
            TestMerchantSeeder::class,
            KoneksiRasaSeeder::class,
            AntarKanMaCourierSeeder::class,
        ]);
    }
}

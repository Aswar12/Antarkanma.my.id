<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Periksa apakah sudah ada user
        $userCount = \App\Models\User::count();
        if ($userCount == 0) {
            // Jika belum ada user, buat beberapa
            \App\Models\User::factory(10)->create();
        }

        // Periksa apakah sudah ada merchant
        $merchantCount = \App\Models\Merchant::count();
        if ($merchantCount == 0) {
            // Jika belum ada merchant, buat beberapa
            \App\Models\Merchant::factory(5)->create();
        }

        // Buat produk
        \App\Models\Product::factory(50)->create();
    }
}

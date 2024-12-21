<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

class ClearAndSeedProductCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        ProductCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create categories
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Berbagai jenis makanan'
            ],
            [
                'name' => 'Minuman',
                'description' => 'Berbagai jenis minuman'
            ],
            [
                'name' => 'Snack',
                'description' => 'Makanan ringan dan cemilan'
            ],
            [
                'name' => 'Buah & Sayur',
                'description' => 'Buah dan sayuran segar'
            ],
            [
                'name' => 'Daging & Ikan',
                'description' => 'Daging dan ikan segar'
            ],
            [
                'name' => 'Bumbu Dapur',
                'description' => 'Bumbu dan rempah-rempah'
            ],
            [
                'name' => 'Bahan Pokok',
                'description' => 'Beras, minyak, dan kebutuhan pokok lainnya'
            ],
            [
                'name' => 'Frozen Food',
                'description' => 'Makanan beku siap saji'
            ]
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}

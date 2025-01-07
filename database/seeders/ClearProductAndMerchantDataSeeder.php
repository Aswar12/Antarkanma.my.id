<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearProductAndMerchantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Clear related tables first
            DB::statement('TRUNCATE TABLE order_items');
            DB::statement('TRUNCATE TABLE orders');
            DB::statement('TRUNCATE TABLE delivery_items');
            DB::statement('TRUNCATE TABLE deliveries');
            DB::statement('TRUNCATE TABLE transactions');
            
            // Then clear product related tables
            DB::statement('TRUNCATE TABLE product_galleries');
            DB::statement('TRUNCATE TABLE product_reviews');
            DB::statement('TRUNCATE TABLE product_variants');
            DB::statement('TRUNCATE TABLE products');
            DB::statement('TRUNCATE TABLE merchants');
            
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearTransactionDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear the tables in reverse order of dependencies
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('transactions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Successfully cleared transaction, order, and order item data.');
    }
}

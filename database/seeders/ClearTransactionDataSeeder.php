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

        // Clear all related tables in reverse order of dependencies
        DB::table('delivery_items')->truncate();
        DB::table('deliveries')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('transactions')->truncate();

        // Reset auto-increment counters
        DB::statement('ALTER TABLE delivery_items AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE deliveries AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE order_items AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 1');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Successfully cleared all transaction-related data:');
        $this->command->info('- Transactions');
        $this->command->info('- Orders');
        $this->command->info('- Order Items');
        $this->command->info('- Deliveries');
        $this->command->info('- Delivery Items');
    }
}

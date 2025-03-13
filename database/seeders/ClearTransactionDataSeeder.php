<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\DeliveryItem;

class ClearTransactionDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Clear all transaction-related tables
            DB::table('delivery_items')->delete();
            DB::table('deliveries')->delete();
            DB::table('order_items')->delete();
            DB::table('orders')->delete();
            DB::table('transactions')->delete();

            // Reset auto-increment counters
            DB::statement('ALTER TABLE delivery_items AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE deliveries AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE order_items AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 1');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->command->info('Successfully cleared and reset all transaction-related tables');

        } catch (\Exception $e) {
            // Re-enable foreign key checks even if an error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->error('Error occurred while clearing data: ' . $e->getMessage());
            throw $e;
        }
    }
}

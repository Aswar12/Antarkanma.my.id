<?php

namespace Database\Seeders;

use App\Models\Courier;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestMultiMerchantTransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // 1. Get existing user and create location
            $user = User::where('roles', 'USER')->where('email', 'user@test.com')->firstOrFail();

            $userLocation = UserLocation::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => 'Jl. Test No. 123',
                    'city' => 'Jakarta',
                    'postal_code' => '12345',
                    'is_default' => true,
                    'address_type' => 'RUMAH'
                ]
            );

            // 2. Get existing merchants
            $merchantA = Merchant::findOrFail(1); // Toko Elektronik Segeri
            $merchantB = Merchant::findOrFail(3); // Toko Bangunan Mandalle

            // 3. Get existing products
            $kipasAngin = Product::findOrFail(1); // Kipas Angin dari Toko Elektronik
            $riceCooker = Product::findOrFail(2); // Rice Cooker dari Toko Elektronik
            $semen = Product::findOrFail(5);      // Semen dari Toko Bangunan

            // 4. Get existing courier
            $courierUser = User::where('roles', 'COURIER')
                              ->where('email', 'courier@test.com')
                              ->firstOrFail();
            
            $courier = Courier::firstOrCreate(
                ['user_id' => $courierUser->id],
                [
                    'vehicle_type' => 'Motor',
                    'license_plate' => 'B 1234 XX'
                ]
            );

            // 5. Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'user_location_id' => $userLocation->id,
                'total_price' => 250000, // Total dari semua order
                'shipping_price' => 30000,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => Transaction::PAYMENT_MANUAL,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'courier_approval' => Transaction::COURIER_PENDING
            ]);

            // 6. Create orders for each merchant
            // Order untuk Merchant A
            $orderA = Order::create([
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'merchant_id' => $merchantA->id,
                'total_amount' => 150000,
                'order_status' => Order::STATUS_PENDING,
                'merchant_approval' => Order::MERCHANT_PENDING
            ]);

            // Order items untuk Merchant A (Toko Elektronik)
            OrderItem::create([
                'order_id' => $orderA->id,
                'product_id' => $kipasAngin->id,
                'quantity' => 1,
                'price' => $kipasAngin->price
            ]);

            OrderItem::create([
                'order_id' => $orderA->id,
                'product_id' => $riceCooker->id,
                'quantity' => 1,
                'price' => $riceCooker->price
            ]);

            // Order untuk Merchant B (Toko Bangunan)
            $orderB = Order::create([
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'merchant_id' => $merchantB->id,
                'total_amount' => $semen->price * 2, // Beli 2 sak semen
                'order_status' => Order::STATUS_PENDING,
                'merchant_approval' => Order::MERCHANT_PENDING
            ]);

            // Order item untuk Merchant B
            OrderItem::create([
                'order_id' => $orderB->id,
                'product_id' => $semen->id,
                'quantity' => 2, // 2 sak semen
                'price' => $semen->price
            ]);

            DB::commit();

            // Output test data info
            $this->command->info('Test transaction created successfully:');
            $this->command->info("Transaction ID: {$transaction->id}");
            $this->command->info("Customer: {$user->name}");
            $this->command->info('Orders:');
            $this->command->info("- Order A (ID: {$orderA->id}): {$merchantA->name}");
            $this->command->info("  * Kipas Angin 16 inch ({$kipasAngin->price})");
            $this->command->info("  * Rice Cooker 1.8L ({$riceCooker->price})");
            $this->command->info("- Order B (ID: {$orderB->id}): {$merchantB->name}");
            $this->command->info("  * Semen 40kg x2 ({$semen->price} x 2)");
            $this->command->info("Courier: {$courier->user->name}");
            $this->command->info('Total: 250,000 + Shipping 30,000');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error creating test data: {$e->getMessage()}");
            throw $e;
        }
    }
}

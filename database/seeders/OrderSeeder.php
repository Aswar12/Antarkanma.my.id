<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Memastikan ada user, product, dan merchant di database
        $users = User::all()->count() > 0 ? User::all() : User::factory()->count(10)->create();
        $products = Product::all()->count() > 0 ? Product::all() : Product::factory()->count(20)->create();
        $merchants = Merchant::all()->count() > 0 ? Merchant::all() : Merchant::factory()->count(5)->create();

        // Membuat 50 order
        Order::factory()
            ->count(50)
            ->create()
            ->each(function ($order) use ($products, $merchants) {
                // Membuat 1-5 item untuk setiap order
                $orderItems = [];
                $totalAmount = 0;

                for ($i = 0; $i < rand(1, 5); $i++) {
                    $product = $products->random();
                    $productVariant = $product->variants()->inRandomOrder()->first();
                    $merchant = $merchants->random();

                    $quantity = rand(1, 5);
                    $price = $product->price + ($productVariant ? $productVariant->price_adjustment : 0);

                    $orderItem = new OrderItem([
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant ? $productVariant->id : null,
                        'merchant_id' => $merchant->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    $orderItems[] = $orderItem;
                    $totalAmount += $quantity * $price;
                }

                // Menyimpan order items
                $order->orderItems()->saveMany($orderItems);

                // Update total amount order
                $order->update([
                    'total_amount' => $totalAmount,
                ]);
            });

        // Memperbarui order_status secara acak
        Order::all()->each(function ($order) {
            $order->update([
                'order_status' => $this->getRandomOrderStatus(),
            ]);
        });
    }

    private function getRandomOrderStatus()
    {
        return ['PENDING', 'COMPLETED', 'CANCELED'][array_rand(['PENDING', 'COMPLETED', 'CANCELED'])];
    }
}

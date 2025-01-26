<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;

class TransactionItemSeeder extends Seeder
{
    public function run()
    {
        $transactions = Transaction::all();
        $products = Product::all();

        foreach ($transactions as $transaction) {
            $numItems = rand(1, 5);
            $totalPrice = 0;
            for ($i = 0; $i < $numItems; $i++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $price = $product->price;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                $totalPrice += $price * $quantity;
            }

            // Update transaction total price
            $transaction->update(['total_price' => $totalPrice + $transaction->shipping_price]);
        }
    }
}

<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $productVariant = ProductVariant::where('product_id', $product->id)->inRandomOrder()->first();

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_variant_id' => $productVariant ? $productVariant->id : null,
            'merchant_id' => $product->merchant_id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $product->price + ($productVariant ? $productVariant->price_adjustment : 0),
        ];
    }
}

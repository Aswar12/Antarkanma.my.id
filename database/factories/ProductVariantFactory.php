<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ProductVariant::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->word,
            'value' => $this->faker->word,
            'price_adjustment' => $this->faker->randomFloat(2, -50, 50),
            'status' => $this->faker->randomElement(['ACTIVE', 'INACTIVE', 'OUT_OF_STOCK']),
        ];
    }
}

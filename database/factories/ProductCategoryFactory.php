<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Snack',
            'Buah & Sayur',
            'Daging & Ikan',
            'Bumbu Dapur',
            'Bahan Pokok',
            'Frozen Food'
        ];
        
        return [
            'name' => $this->faker->unique()->randomElement($categories),
        ];
    }
}

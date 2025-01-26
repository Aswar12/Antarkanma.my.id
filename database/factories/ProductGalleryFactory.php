<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductGalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get list of existing images from storage
        $images = Storage::disk('public')->files('product-galleries');
        
        return [
            'product_id' => Product::factory(),
            'url' => $this->faker->randomElement($images)
        ];
    }
}

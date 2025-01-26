<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Delivery::class;

    public function definition()
    {
        return [
            'transaction_id' => Transaction::factory(),
            'tracking_number' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{8}'),
            'status' => $this->faker->randomElement(['PENDING', 'SHIPPING', 'DELIVERED']),
            'estimated_arrival' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'actual_arrival' => $this->faker->optional(0.7)->dateTimeBetween('+1 day', '+2 weeks'),
        ];
    }
}

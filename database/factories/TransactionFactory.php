<?php

namespace Database\Factories;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\UserLocation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'order_id' => function () {
                return \App\Models\Order::factory()->create()->id;
            },
            'user_location_id' => UserLocation::factory(),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'shipping_price' => $this->faker->randomFloat(2, 5, 50),
            'payment_date' => $this->faker->optional()->dateTime(),
            'status' => $this->faker->randomElement(['PENDING', 'COMPLETED', 'CANCELED']),
            'payment_method' => $this->faker->randomElement(['MANUAL', 'ONLINE']),
            'payment_status' => $this->faker->randomElement(['PENDING', 'COMPLETED', 'FAILED']),
            'rating' => $this->faker->optional()->numberBetween(1, 5),
            'note' => $this->faker->optional()->sentence,
        ];
    }
}

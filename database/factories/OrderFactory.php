<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'payment_status' => $this->faker->randomElement(['PENDING', 'COMPLETED', 'FAILED']),
            'order_status' => $this->faker->randomElement(['PENDING', 'COMPLETED', 'CANCELED']),
        ];
    }
}

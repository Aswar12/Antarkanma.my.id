<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Courier;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Courier::class;

    public function definition()
    {
        return [
            'user_id' => User::factory()->create(['roles' => 'COURIER'])->id,
            'vehicle_type' => $this->faker->randomElement(['motorcycle', 'car', 'bicycle']),
            'license_plate' => $this->faker->regexify('[A-Z]{1,3}-[0-9]{1,4}-[A-Z]{1,3}'),
        ];
    }
}

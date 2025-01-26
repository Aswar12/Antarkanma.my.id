<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Merchant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    protected $model = Merchant::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $operatingDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $is24Hours = $this->faker->boolean(20); // 20% chance of being 24 hours

        return [
            'name' => $this->faker->company,
            'owner_id' => User::factory(),
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'opening_time' => $is24Hours ? null : $this->faker->time('H:i:s', '10:00:00'),
            'closing_time' => $is24Hours ? null : $this->faker->time('H:i:s', '22:00:00'),
            'is_open_24_hours' => $is24Hours,
            'operating_days' => $this->faker->randomElements($operatingDays, $this->faker->numberBetween(5, 7)),
        ];
    }
}

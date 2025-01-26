<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserLocation;

class UserLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Buat lokasi untuk setiap pengguna
        User::all()->each(function ($user) {
            UserLocation::factory()->count(rand(1, 3))->create([
                'user_id' => $user->id
            ]);
        });

        // Pastikan setiap user memiliki satu lokasi default
        User::with('userLocations')->get()->each(function ($user) {
            if ($user->userLocations->isNotEmpty()) {
                if (!$user->userLocations->where('is_default', true)->first()) {
                    $user->userLocations->random()->update(['is_default' => true]);
                }
            }
        });
    }
}

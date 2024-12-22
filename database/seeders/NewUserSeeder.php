<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NewUserSeeder extends Seeder
{
    public function run()
    {
        // Clear existing users
        DB::table('users')->truncate();

        // Create 20 users
        User::factory()->count(20)->create();
    }
}

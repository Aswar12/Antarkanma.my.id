<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    public function run()
    {
        // Create Merchant Account
        User::create([
            'name' => 'Test Merchant',
            'email' => 'merchant@test.com', 
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567891',
            'roles' => 'MERCHANT',
            'username' => 'testmerchant',
            'is_active' => true
        ]);

        // Create Courier Account
        User::create([
            'name' => 'Test Courier',
            'email' => 'courier@test.com',
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567892',
            'roles' => 'COURIER',
            'username' => 'testcourier',
            'is_active' => true
        ]);

        // Create Regular User Account
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('aswar123'),
            'phone_number' => '081234567893',
            'roles' => 'USER',
            'username' => 'testuser',
            'is_active' => true
        ]);
    }
}

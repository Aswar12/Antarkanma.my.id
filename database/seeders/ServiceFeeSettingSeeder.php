<?php

namespace Database\Seeders;

use App\Models\ServiceFeeSetting;
use Illuminate\Database\Seeder;

class ServiceFeeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if service fee setting already exists
        if (ServiceFeeSetting::count() > 0) {
            return;
        }

        // Create initial service fee setting
        ServiceFeeSetting::create([
            'service_fee' => 500.00,
            'is_active' => true,
            'updated_by' => 'System Seeder',
            'notes' => 'Initial service fee setting - Rp 500 per transaksi (bukan per order)',
        ]);
    }
}

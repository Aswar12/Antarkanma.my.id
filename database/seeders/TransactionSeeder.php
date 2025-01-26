<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\Courier;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // Buat transaksi
        Transaction::factory(50)->create()->each(function ($transaction) {
            // Tambahkan logika tambahan jika diperlukan
        });
    }
}

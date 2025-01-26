<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Delivery;

class DeliverySeeder extends Seeder
{
    public function run()
    {
        $transactions = Transaction::all();

        foreach ($transactions as $transaction) {
            Delivery::create([
                'transaction_id' => $transaction->id,
                'tracking_number' => strtoupper(substr(md5(microtime()), 0, 10)),
                'status' => $this->getRandomStatus(),
                'estimated_arrival' => now()->addDays(rand(1, 7)),
                'actual_arrival' => $this->getActualArrival(),
            ]);
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['PENDING', 'SHIPPING', 'DELIVERED'];
        return $statuses[array_rand($statuses)];
    }

    private function getActualArrival()
    {
        // 70% chance of having an actual arrival date
        return (rand(1, 10) <= 7) ? now()->addDays(rand(1, 14)) : null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    public function getHomeStatistics()
    {
        return Cache::remember('home_statistics', 3600, function () {
            $statistics = [
                'merchants_count' => '0',
                'users_count' => '0',
                'courier_count' => '0',
                'successful_deliveries' => '0',
                'product_count' => '0',
                'cities_served' => 3
            ];

            // Get merchant count
            try {
                $statistics['merchants_count'] = Merchant::where('status', 'active')->count() ?: '0';
            } catch (\Exception $e) {
                \Log::error('Error fetching merchant count: ' . $e->getMessage());
            }

            // Get registered users count
            try {
                $statistics['users_count'] = User::where('roles', 'USER')->count() ?: '0';
            } catch (\Exception $e) {
                \Log::error('Error fetching users count: ' . $e->getMessage());
            }

            // Get courier count
            try {
                $statistics['courier_count'] = User::where('roles', 'COURIER')->count() ?: '0';
            } catch (\Exception $e) {
                \Log::error('Error fetching courier count: ' . $e->getMessage());
            }

            // Get successful deliveries
            try {
                $statistics['successful_deliveries'] = Order::where('order_status', 'COMPLETED')->count() ?: '0';
            } catch (\Exception $e) {
                \Log::error('Error fetching successful deliveries: ' . $e->getMessage());
            }

            // Get product count
            try {
                $statistics['product_count'] = Product::count() ?: '0';
            } catch (\Exception $e) {
                \Log::error('Error fetching product count: ' . $e->getMessage());
            }

            return $statistics;
        });
    }
}

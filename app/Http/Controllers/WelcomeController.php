<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeController extends Controller
{
    public function index()
    {
        $merchants = Merchant::with(['user', 'products.reviews'])
            ->where('is_active', true)
            ->withCount('orders as total_orders')
            ->get()
            ->map(function ($merchant) {
                // Calculate average rating from product reviews using raw SQL
                $rating = \DB::table('products')
                    ->join('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                    ->where('products.merchant_id', $merchant->id)
                    ->avg('product_reviews.rating') ?? 0;

                $merchant->rating = round($rating, 1);

                // Logo URL is handled by the model accessor

                // Set distance (to be implemented later)
                $merchant->distance = 0;

                // Remove the products relationship from the output
                unset($merchant->products);

                return $merchant;
            });

        return view('welcome', [
            'merchants' => $merchants
        ]);
    }
}

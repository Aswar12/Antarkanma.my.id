<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProductReviewSeeder extends Seeder
{
    private $comments = [
        1 => [
            'Produk tidak sesuai harapan',
            'Kualitas sangat buruk',
            'Pelayanan mengecewakan',
            'Tidak recommended',
            'Harga tidak sebanding'
        ],
        2 => [
            'Produk biasa saja',
            'Perlu perbaikan',
            'Pelayanan kurang',
            'Harga terlalu mahal',
            'Pengiriman lambat'
        ],
        3 => [
            'Produk cukup bagus',
            'Sesuai harga',
            'Pelayanan standar',
            'Lumayan',
            'Pengiriman tepat waktu'
        ],
        4 => [
            'Produk bagus',
            'Kualitas baik',
            'Pelayanan memuaskan',
            'Recommended',
            'Harga terjangkau'
        ],
        5 => [
            'Produk sangat bagus',
            'Kualitas premium',
            'Pelayanan sangat baik',
            'Highly recommended',
            'Worth it'
        ]
    ];

    public function run()
    {
        // Clear existing reviews
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_reviews')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get existing users and products
        $users = User::all();
        $products = Product::all();

        // Create reviews
        foreach ($products as $product) {
            // Each product gets 3-7 reviews
            $numReviews = rand(3, 7);
            
            // Get random users for this product
            $reviewers = $users->random($numReviews);
            
            foreach ($reviewers as $user) {
                $rating = rand(3, 5); // Mostly positive ratings
                
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $this->comments[$rating][array_rand($this->comments[$rating])]
                ]);
            }
        }
    }
}

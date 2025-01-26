<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductReviewSeeder extends Seeder
{
    private $reviewComments = [
        5 => [
            'Produk sangat berkualitas, sangat memuaskan!',
            'Kualitas produk luar biasa, sangat recommended!',
            'Pengiriman cepat dan produk sesuai deskripsi',
            'Harga sebanding dengan kualitas, worth it!',
            'Pelayanan sangat baik, produk original',
            'Sangat puas dengan pembelian ini!',
            'Kualitas premium, tidak mengecewakan',
            'Produk authentic dan sesuai gambar',
        ],
        4 => [
            'Produk bagus, sesuai dengan harga',
            'Kualitas baik, akan pesan lagi',
            'Sesuai ekspektasi, cukup puas',
            'Produk original, packaging aman',
            'Harga reasonable untuk kualitasnya',
            'Pengiriman cepat, produk bagus',
            'Recommended seller, produk berkualitas',
            'Barang sesuai deskripsi, puas',
        ],
        3 => [
            'Produk standar, masih bisa ditingkatkan',
            'Kualitas cukup baik untuk harganya',
            'Pengiriman agak lama, tapi produk bagus',
            'Harga standard untuk kualitas segini',
            'Lumayan, sesuai harga',
            'Packaging bisa lebih baik',
            'Produk ok, pengiriman bisa dipercepat',
            'Cukup puas dengan produknya',
        ]
    ];

    private $categoryRatings = [
        'Makanan' => [4, 5],
        'Minuman' => [4, 5],
        'Electronics' => [3, 5],
        'Fashion' => [3, 5],
        'Home & Living' => [3, 5],
        'Sports & Outdoors' => [3, 5]
    ];

    public function run()
    {
        // Clear existing reviews
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_reviews')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get all users
        $users = User::where('roles', '!=', 'MERCHANT')->get();
        if ($users->count() < 5) {
            // Create some users if not enough exist
            $needed = 5 - $users->count();
            User::factory($needed)->create(['roles' => 'USER']);
            $users = User::where('roles', '!=', 'MERCHANT')->get();
        }

        // Get all products
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            $categoryName = $product->category->name;
            
            // Get rating range for this category
            $ratingRange = $this->categoryRatings[$categoryName] ?? [3, 5];

            // Generate 3-8 reviews for each product
            $numReviews = rand(3, 8);
            $reviewUsers = $users->random($numReviews);

            foreach ($reviewUsers as $user) {
                // Generate rating based on category popularity
                $rating = rand($ratingRange[0], $ratingRange[1]);

                // Get random comment for this rating
                $comments = $this->reviewComments[$rating];
                $comment = $comments[array_rand($comments)];

                ProductReview::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => $comment
                ]);
            }
        }
    }
}

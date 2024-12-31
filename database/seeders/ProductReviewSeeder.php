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
            'Rasanya enak sekali, pasti pesan lagi!',
            'Pengiriman cepat dan produk sesuai deskripsi',
            'Harga sebanding dengan kualitas, recommended!',
            'Pelayanan sangat baik, produk fresh',
        ],
        4 => [
            'Produk bagus, sesuai dengan harga',
            'Rasa enak, tapi pengiriman agak lama',
            'Kualitas baik, akan pesan lagi',
            'Sesuai ekspektasi, lumayan puas',
            'Produk fresh, tapi packaging bisa ditingkatkan',
        ],
        3 => [
            'Produk standar, masih bisa ditingkatkan',
            'Rasa biasa saja, tapi masih ok',
            'Pengiriman agak lama, tapi produk bagus',
            'Harga sedikit mahal untuk kualitas segini',
            'Lumayan, tapi masih ada yang perlu diperbaiki',
        ],
        2 => [
            'Kualitas dibawah ekspektasi',
            'Pengiriman terlalu lama',
            'Harga tidak sebanding dengan kualitas',
            'Produk kurang fresh',
            'Perlu peningkatan kualitas',
        ],
        1 => [
            'Sangat mengecewakan',
            'Tidak sesuai deskripsi',
            'Kualitas buruk',
            'Tidak recommended',
            'Pelayanan kurang baik',
        ],
    ];

    private $productPopularity = [
        'Makanan' => [
            'Nasi Goreng Special' => [4, 5],    // Popular
            'Mie Goreng Special' => [4, 5],     // Popular
            'Ayam Goreng Kremes' => [4, 5],     // Popular
            'Rendang Daging' => [4, 5],         // Popular
        ],
        'Minuman' => [
            'Es Teh Manis' => [4, 5],           // Popular
            'Jus Alpukat' => [4, 5],            // Popular
            'Es Jeruk Peras' => [3, 5],         // Moderate
        ],
        'Snack' => [
            'Kentang Goreng' => [4, 5],         // Popular
            'Pisang Goreng' => [3, 5],          // Moderate
            'Tahu Crispy' => [3, 5],            // Moderate
        ],
        'Buah & Sayur' => [
            'Apel Fuji' => [4, 5],              // Popular
            'Jeruk Mandarin' => [3, 5],         // Moderate
        ],
        'Daging & Ikan' => [
            'Daging Sapi Segar' => [3, 5],      // Moderate
            'Ikan Salmon' => [4, 5],            // Popular
        ],
        'Bumbu Dapur' => [
            'Bawang Putih' => [3, 5],           // Moderate
            'Cabai Merah' => [3, 5],            // Moderate
        ],
        'Bahan Pokok' => [
            'Beras Premium' => [4, 5],          // Popular
            'Minyak Goreng' => [3, 5],          // Moderate
        ],
        'Frozen Food' => [
            'Nugget Ayam' => [4, 5],            // Popular
            'Bakso Sapi' => [3, 5],             // Moderate
        ],
    ];

    public function run()
    {
        // Clear existing reviews
        DB::table('product_reviews')->truncate();

        // Get all users
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            // Create some users if none exist
            User::factory(10)->create();
            $userIds = User::pluck('id')->toArray();
        }

        // Get all products for merchant ID 506
        $products = Product::with('category')->where('merchant_id', 506)->get();

        foreach ($products as $product) {
            $categoryName = $product->category->name;
            $productName = $product->name;

            // Get popularity range for this product
            $ratingRange = $this->productPopularity[$categoryName][$productName] ?? [1, 5];

            // Generate 5-20 reviews for each product
            $numReviews = rand(5, 20);

            for ($i = 0; $i < $numReviews; $i++) {
                // Generate rating based on product popularity
                $rating = rand($ratingRange[0], $ratingRange[1]);

                // Get random comment for this rating
                $comments = $this->reviewComments[$rating];
                $comment = $comments[array_rand($comments)];

                ProductReview::create([
                    'user_id' => $userIds[array_rand($userIds)],
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => $comment
                ]);
            }
        }
    }
}

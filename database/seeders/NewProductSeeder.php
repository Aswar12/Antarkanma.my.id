<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;

class NewProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // Makanan
            [
                'category_name' => 'Makanan',
                'products' => [
                    [
                        'name' => 'Nasi Goreng Spesial',
                        'price' => 25000,
                        'description' => 'Nasi goreng dengan telur, ayam, dan sayuran'
                    ],
                    [
                        'name' => 'Mie Goreng',
                        'price' => 20000,
                        'description' => 'Mie goreng dengan telur dan sayuran'
                    ]
                ]
            ],
            // Minuman
            [
                'category_name' => 'Minuman',
                'products' => [
                    [
                        'name' => 'Es Teh Manis',
                        'price' => 5000,
                        'description' => 'Teh manis dingin'
                    ],
                    [
                        'name' => 'Jus Alpukat',
                        'price' => 15000,
                        'description' => 'Jus alpukat segar'
                    ]
                ]
            ],
            // Snack
            [
                'category_name' => 'Snack',
                'products' => [
                    [
                        'name' => 'Kentang Goreng',
                        'price' => 15000,
                        'description' => 'Kentang goreng crispy'
                    ],
                    [
                        'name' => 'Pisang Goreng',
                        'price' => 10000,
                        'description' => 'Pisang goreng crispy'
                    ]
                ]
            ],
            // Buah & Sayur
            [
                'category_name' => 'Buah & Sayur',
                'products' => [
                    [
                        'name' => 'Apel Fuji',
                        'price' => 25000,
                        'description' => 'Apel fuji segar per kg'
                    ],
                    [
                        'name' => 'Bayam',
                        'price' => 5000,
                        'description' => 'Bayam segar per ikat'
                    ]
                ]
            ],
            // Daging & Ikan
            [
                'category_name' => 'Daging & Ikan',
                'products' => [
                    [
                        'name' => 'Daging Sapi',
                        'price' => 120000,
                        'description' => 'Daging sapi segar per kg'
                    ],
                    [
                        'name' => 'Ikan Salmon',
                        'price' => 150000,
                        'description' => 'Ikan salmon segar per kg'
                    ]
                ]
            ],
            // Bumbu Dapur
            [
                'category_name' => 'Bumbu Dapur',
                'products' => [
                    [
                        'name' => 'Bawang Putih',
                        'price' => 30000,
                        'description' => 'Bawang putih per kg'
                    ],
                    [
                        'name' => 'Cabai Merah',
                        'price' => 40000,
                        'description' => 'Cabai merah per kg'
                    ]
                ]
            ],
            // Bahan Pokok
            [
                'category_name' => 'Bahan Pokok',
                'products' => [
                    [
                        'name' => 'Beras',
                        'price' => 70000,
                        'description' => 'Beras premium per 5kg'
                    ],
                    [
                        'name' => 'Minyak Goreng',
                        'price' => 25000,
                        'description' => 'Minyak goreng per 2L'
                    ]
                ]
            ],
            // Frozen Food
            [
                'category_name' => 'Frozen Food',
                'products' => [
                    [
                        'name' => 'Nugget Ayam',
                        'price' => 45000,
                        'description' => 'Nugget ayam beku 500g'
                    ],
                    [
                        'name' => 'Sosis Sapi',
                        'price' => 35000,
                        'description' => 'Sosis sapi beku 300g'
                    ]
                ]
            ]
        ];

        foreach ($products as $categoryProducts) {
            $category = ProductCategory::where('name', $categoryProducts['category_name'])->first();
            
            if ($category) {
                foreach ($categoryProducts['products'] as $product) {
                    Product::create([
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'description' => $product['description'],
                        'category_id' => $category->id,
                        'merchant_id' => 1, // Assuming merchant_id is required
                        'status' => 'active'
                    ]);
                }
            }
        }
    }
}

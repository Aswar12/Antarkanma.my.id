<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestMerchantSeeder extends Seeder
{
    private function uploadFileToS3($localPath, $s3Path) 
    {
        try {
            // Use absolute path for local files
            $absolutePath = storage_path('app/public/' . $localPath);
            
            if (!file_exists($absolutePath)) {
                $this->command->error("Local file not found: {$absolutePath}");
                return false;
            }

            $fileContent = file_get_contents($absolutePath);
            if ($fileContent === false) {
                $this->command->error("Failed to read file: {$absolutePath}");
                return false;
            }
            
            // Determine content type based on file extension
            $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            $contentType = match($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };
            
            // Ensure the directory exists in S3
            $directory = dirname($s3Path);
            if (!Storage::disk('s3')->exists($directory)) {
                Storage::disk('s3')->makeDirectory($directory);
            }
            
            // Upload to S3 with public visibility
            try {
                $s3Client = Storage::disk('s3')->getClient();
                $bucket = config('filesystems.disks.s3.bucket');
                
                $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $s3Path,
                    'Body' => $fileContent,
                    'ContentType' => $contentType,
                    'ACL' => 'public-read',
                    'CacheControl' => 'max-age=31536000'
                ]);

                $this->command->info("Path to store in database: {$s3Path}");
                $uploaded = true;
            } catch (\Exception $e) {
                $this->command->error("S3 upload error: " . $e->getMessage());
                $uploaded = false;
            }

            if (!$uploaded) {
                $this->command->error("Failed to upload file to S3: {$s3Path}");
                return false;
            }

            $url = Storage::disk('s3')->url($s3Path);
            $this->command->info("Successfully uploaded {$localPath} to S3");
            $this->command->info("File accessible at: {$url}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to upload file to S3", [
                'localPath' => $localPath,
                's3Path' => $s3Path,
                'error' => $e->getMessage()
            ]);
            $this->command->error("Failed to upload {$localPath} to S3: " . $e->getMessage());
            return false;
        }
    }

    private $merchantData = [
        [
            'user' => [
                'name' => 'Warung Pak Aswar',
                'email' => 'warung.aswar@test.com',
                'password' => 'aswar123',
                'phone_number' => '081234567894',
                'username' => 'warungaswar',
            ],
            'merchant' => [
                'name' => 'Warung Pak Aswar',
                'address' => 'Jl. Poros Segeri No. 456',
                'phone_number' => '081234567894',
                'description' => 'Warung makan tradisional dengan masakan rumahan',
                'opening_time' => '07:00',
                'closing_time' => '21:00',
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'latitude' => -4.635991,
                'longitude' => 119.587339,
            ],
            'products' => [
                'Makanan' => [
                    [
                        'name' => 'Nasi Kuning Special',
                        'price' => 20000,
                        'description' => 'Nasi kuning dengan telur, ayam goreng, dan sambal',
                        'images' => [
                            'product-galleries/pexels-kelvinocta16-7190355.jpg',
                            'product-galleries/pexels-kelvinocta16-7190356.jpg'
                        ]
                    ],
                    [
                        'name' => 'Gado-gado',
                        'price' => 15000,
                        'description' => 'Sayuran segar dengan bumbu kacang',
                        'images' => [
                            'product-galleries/pexels-meggy-kadam-aryanto-3797063-7429247.jpg',
                            'product-galleries/pexels-meggy-kadam-aryanto-3797063-9620137.jpg'
                        ]
                    ]
                ],
                'Minuman' => [
                    [
                        'name' => 'Es Kelapa Muda',
                        'price' => 8000,
                        'description' => 'Kelapa muda segar dengan es',
                        'images' => [
                            'product-galleries/pexels-pixabay-372851.jpg',
                            'product-galleries/pexels-polina-tankilevitch-4110012.jpg'
                        ]
                    ]
                ]
            ]
        ],
        [
            'user' => [
                'name' => 'Cafe Blacky',
                'email' => 'cafe.blacky@test.com',
                'password' => 'aswar123',
                'phone_number' => '081234567895',
                'username' => 'cafeblacky',
            ],
            'merchant' => [
                'name' => 'Cafe Blacky',
                'address' => 'Jl. Poros Segeri No. 789',
                'phone_number' => '081234567895',
                'description' => 'Cafe modern dengan menu western dan coffee',
                'opening_time' => '10:00',
                'closing_time' => '22:00',
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                'latitude' => -4.638991,
                'longitude' => 119.589339,
            ],
            'products' => [
                'Makanan' => [
                    [
                        'name' => 'Beef Burger',
                        'price' => 35000,
                        'description' => 'Burger dengan daging sapi premium',
                        'images' => [
                            'product-galleries/pexels-pixabay-279906.jpg',
                            'product-galleries/pexels-pixabay-372851.jpg'
                        ]
                    ],
                    [
                        'name' => 'French Fries',
                        'price' => 20000,
                        'description' => 'Kentang goreng crispy dengan saus',
                        'images' => [
                            'product-galleries/pexels-pixabay-279906.jpg',
                            'product-galleries/pexels-pixabay-372851.jpg'
                        ]
                    ]
                ],
                'Minuman' => [
                    [
                        'name' => 'Cappuccino',
                        'price' => 25000,
                        'description' => 'Kopi dengan foam susu yang lembut',
                        'images' => [
                            'product-galleries/pexels-pixabay-372851.jpg',
                            'product-galleries/pexels-polina-tankilevitch-4110012.jpg'
                        ]
                    ],
                    [
                        'name' => 'Matcha Latte',
                        'price' => 28000,
                        'description' => 'Green tea dengan susu premium',
                        'images' => [
                            'product-galleries/pexels-pixabay-372851.jpg',
                            'product-galleries/pexels-polina-tankilevitch-4110012.jpg'
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Test S3 connection
            try {
                $testContent = 'This is a test file to verify storage connection';
                Storage::disk('s3')->put('test/connection-test.txt', $testContent);
                $this->command->info('S3 connection test successful');
                Storage::disk('s3')->delete('test/connection-test.txt');
            } catch (\Exception $e) {
                Log::error("S3 connection test failed", ['error' => $e->getMessage()]);
                $this->command->error('S3 connection test failed: ' . $e->getMessage());
                return;
            }

            foreach ($this->merchantData as $data) {
                // Create Merchant Account
                $merchantUser = User::create([
                    'name' => $data['user']['name'],
                    'email' => $data['user']['email'],
                    'password' => Hash::make($data['user']['password']),
                    'phone_number' => $data['user']['phone_number'],
                    'roles' => 'MERCHANT',
                    'username' => $data['user']['username'],
                    'is_active' => true
                ]);

                // Upload merchant profile photo
                $profilePhotoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
                $s3ProfilePhotoPath = 'profile-photos/merchant-' . $merchantUser->id . '.jpg';
                
                if ($this->uploadFileToS3($profilePhotoPath, $s3ProfilePhotoPath)) {
                    $merchantUser->forceFill([
                        'profile_photo_path' => $s3ProfilePhotoPath
                    ])->save();
                }

                // Create merchant
                $merchant = Merchant::create([
                    'owner_id' => $merchantUser->id,
                    'name' => $data['merchant']['name'],
                    'address' => $data['merchant']['address'],
                    'phone_number' => $data['merchant']['phone_number'],
                    'status' => 'active',
                    'description' => $data['merchant']['description'],
                    'opening_time' => Carbon::createFromTimeString($data['merchant']['opening_time']),
                    'closing_time' => Carbon::createFromTimeString($data['merchant']['closing_time']),
                    'operating_days' => $data['merchant']['operating_days'],
                ]);

                // Upload merchant logo
                $localLogoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
                $s3LogoPath = 'merchants/logos/merchant-' . $merchant->id . '.jpg';
                if ($this->uploadFileToS3($localLogoPath, $s3LogoPath)) {
                    $merchant->update(['logo' => $s3LogoPath]);
                }

                // Create merchant location
                UserLocation::create([
                    'user_id' => $merchantUser->id,
                    'address' => $data['merchant']['address'],
                    'district' => 'Segeri',
                    'city' => 'Pangkep',
                    'postal_code' => '90655',
                    'latitude' => $data['merchant']['latitude'],
                    'longitude' => $data['merchant']['longitude'],
                    'address_type' => 'Toko',
                    'phone_number' => $data['merchant']['phone_number'],
                    'is_default' => true,
                    'is_active' => true,
                    'country' => 'Indonesia'
                ]);

                // Create categories and products
                foreach ($data['products'] as $categoryName => $products) {
                    $category = ProductCategory::firstOrCreate([
                        'name' => $categoryName
                    ]);

                    foreach ($products as $productData) {
                        $product = Product::create([
                            'merchant_id' => $merchant->id,
                            'category_id' => $category->id,
                            'name' => $productData['name'],
                            'description' => $productData['description'],
                            'price' => $productData['price'],
                            'status' => 'active'
                        ]);

                        // Create product galleries
                        foreach ($productData['images'] as $index => $imagePath) {
                            $s3ImagePath = 'products/images/' . $product->id . '-' . $index . '-' . basename($imagePath);
                            
                            if ($this->uploadFileToS3($imagePath, $s3ImagePath)) {
                                ProductGallery::create([
                                    'product_id' => $product->id,
                                    'url' => $s3ImagePath
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            $this->command->info('Test merchants seeded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}

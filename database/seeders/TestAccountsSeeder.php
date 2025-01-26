<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use App\Models\ProductReview;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestAccountsSeeder extends Seeder
{
    private $products = [
        'Makanan' => [
            [
                'name' => 'Nasi Goreng Special',
                'price' => 25000,
                'description' => 'Nasi goreng dengan telur, ayam, dan sayuran pilihan',
                'images' => [
                    'pexels-kelvinocta16-7190355.jpg',
                    'pexels-kelvinocta16-7190356.jpg',
                    'pexels-kelvinocta16-7190359.jpg'
                ]
            ],
            [
                'name' => 'Mie Goreng Special',
                'price' => 23000,
                'description' => 'Mie goreng dengan telur dan sayuran segar',
                'images' => [
                    'pexels-meggy-kadam-aryanto-3797063-7429247.jpg',
                    'pexels-meggy-kadam-aryanto-3797063-9620137.jpg',
                    'pexels-meggy-kadam-aryanto-3797063-11346027.jpg'
                ]
            ],
            [
                'name' => 'Ayam Goreng Kremes',
                'price' => 30000,
                'description' => 'Ayam goreng dengan bumbu kremes renyah',
                'images' => [
                    'pexels-ffawdyy-13294514.jpg',
                    'pexels-ffawdyy-13294534.jpg',
                    'pexels-ffawdyy-13294536.jpg'
                ]
            ]
        ],
        'Minuman' => [
            [
                'name' => 'Es Teh Manis',
                'price' => 5000,
                'description' => 'Teh manis dingin segar',
                'images' => [
                    'pexels-pixabay-372851.jpg',
                    'pexels-polina-tankilevitch-4110012.jpg',
                    'pexels-pragyanbezbo-2010701.jpg'
                ]
            ],
            [
                'name' => 'Es Jeruk',
                'price' => 7000,
                'description' => 'Jeruk peras segar dengan es',
                'images' => [
                    'pexels-tijana-drndarski-449691-3338681.jpg',
                    'pexels-tijana-drndarski-449691-3656119.jpg',
                    'pexels-pixabay-279906.jpg'
                ]
            ]
        ]
    ];

    private $reviewComments = [
        'Sangat enak dan pelayanan ramah!',
        'Makanannya fresh dan cepat sampai',
        'Recommended banget, harga terjangkau',
        'Rasa mantap, porsi pas',
        'Puas dengan pelayanannya'
    ];

    private function uploadFileToS3($localPath, $s3Path) 
    {
        try {
            // Remove 'public/' prefix if exists since Storage::disk('local') already points to storage/app
            $localPath = str_replace('public/', '', $localPath);
            
            if (Storage::disk('local')->exists($localPath)) {
                $fileContent = Storage::disk('local')->get($localPath);
                
                // Determine content type based on file extension
                $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                $contentType = match($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'application/octet-stream',
                };
                
                // Upload to S3 with public visibility and proper headers
                Storage::disk('s3')->put($s3Path, $fileContent, [
                    'visibility' => 'public',
                    'ContentType' => $contentType,
                    'CacheControl' => 'max-age=31536000, public'
                ]);
                
                // Get the S3 URL
                $url = Storage::disk('s3')->url($s3Path);
                $this->command->info("Successfully uploaded {$localPath} to S3");
                $this->command->info("File accessible at: {$url}");
                
                // Verify the file exists and is accessible
                if (Storage::disk('s3')->exists($s3Path)) {
                    $this->command->info("File verified on S3");
                    return true;
                } else {
                    throw new \Exception("File uploaded but not verified on S3");
                }
            } else {
                $this->command->error("Local file not found in storage/app/{$localPath}");
                return false;
            }
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

            // Create Merchant Account with profile photo
            $merchantUser = User::create([
                'name' => 'Test Merchant',
                'email' => 'merchant@test.com', 
                'password' => Hash::make('aswar123'),
                'phone_number' => '081234567891',
                'roles' => 'MERCHANT',
                'username' => 'testmerchant',
                'is_active' => true
            ]);

            // Upload merchant profile photo if exists
            $profilePhotoPath = '/home/antarkanma/Desktop/Antarkanma.my.id/storage/app/public/profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
            $s3ProfilePhotoPath = 'profile-photos/merchant-' . $merchantUser->id . '.jpg';
            
            try {
                if (file_exists($profilePhotoPath)) {
                    $fileContent = file_get_contents($profilePhotoPath);
                    
                    // Upload to S3 with public visibility and proper headers
                    Storage::disk('s3')->put($s3ProfilePhotoPath, $fileContent, [
                        'visibility' => 'public',
                        'ContentType' => 'image/jpeg',
                        'CacheControl' => 'max-age=31536000, public'
                    ]);
                    
                    // Update user profile photo path
                    $merchantUser->forceFill([
                        'profile_photo_path' => $s3ProfilePhotoPath
                    ])->save();
                    
                    $this->command->info("Successfully uploaded profile photo to S3: " . Storage::disk('s3')->url($s3ProfilePhotoPath));
                } else {
                    $this->command->error("Profile photo not found in storage/app/{$profilePhotoPath}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to upload profile photo to S3", [
                    'error' => $e->getMessage(),
                    'path' => $profilePhotoPath
                ]);
                $this->command->error("Failed to upload profile photo: " . $e->getMessage());
            }

            // Upload merchant logo from local storage
            $localLogoPath = 'merchants/logos/merchant-logo.png';
            $s3LogoPath = 'merchants/logos/merchant-' . $merchantUser->id . '.png';
            $logoUploaded = $this->uploadFileToS3($localLogoPath, $s3LogoPath);

            $merchant = Merchant::create([
                'owner_id' => $merchantUser->id,
                'name' => 'Test Merchant Store',
                'address' => 'Jl. Poros Segeri No. 123',
                'phone_number' => '081234567891',
                'status' => 'active',
                'description' => 'Warung makan dan minuman dengan berbagai menu special',
                'opening_time' => Carbon::createFromTime(8, 0, 0),
                'closing_time' => Carbon::createFromTime(22, 0, 0),
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                'logo' => $logoUploaded ? $s3LogoPath : null
            ]);

            // Create merchant location
            UserLocation::create([
                'user_id' => $merchantUser->id,
                'address' => 'Jl. Poros Segeri No. 123',
                'district' => 'Segeri',
                'city' => 'Pangkep',
                'postal_code' => '90655',
                'latitude' => -4.632991,
                'longitude' => 119.585339,
                'address_type' => 'Toko',
                'phone_number' => '081234567891',
                'is_default' => true,
                'is_active' => true,
                'country' => 'Indonesia'
            ]);

            // Create Courier Account
            $courierUser = User::create([
                'name' => 'Test Courier',
                'email' => 'courier@test.com',
                'password' => Hash::make('aswar123'),
                'phone_number' => '081234567892',
                'roles' => 'COURIER',
                'username' => 'testcourier',
                'is_active' => true
            ]);

            // Create Regular User Account for reviews
            $user = User::create([
                'name' => 'Test User',
                'email' => 'user@test.com',
                'password' => Hash::make('aswar123'),
                'phone_number' => '081234567893',
                'roles' => 'USER',
                'username' => 'testuser',
                'is_active' => true
            ]);

            // Create categories and products with image upload tests
            foreach ($this->products as $categoryName => $products) {
                $category = ProductCategory::create([
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

                    // Upload product images from local storage
                    foreach ($productData['images'] as $index => $image) {
                        $localImagePath = 'products/images/' . $image;
                        $s3ImagePath = 'products/images/' . $product->id . '-' . $index . '-' . $image;
                        
                        if ($this->uploadFileToS3($localImagePath, $s3ImagePath)) {
                            ProductGallery::create([
                                'product_id' => $product->id,
                                'url' => $s3ImagePath
                            ]);
                        }
                    }

                    // Create 2 reviews for each product
                    for ($i = 0; $i < 2; $i++) {
                        ProductReview::create([
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'rating' => rand(4, 5),
                            'comment' => $this->reviewComments[array_rand($this->reviewComments)]
                        ]);
                    }
                }
            }

            DB::commit();
            $this->command->info('All seeding completed successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Seeding failed", ['error' => $e->getMessage()]);
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}

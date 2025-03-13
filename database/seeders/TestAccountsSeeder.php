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
                    'product-galleries/pexels-kelvinocta16-7190355.jpg',
                    'product-galleries/pexels-kelvinocta16-7190356.jpg',
                    'product-galleries/pexels-kelvinocta16-7190359.jpg'
                ]
            ],
            [
                'name' => 'Mie Goreng Special',
                'price' => 23000,
                'description' => 'Mie goreng dengan telur dan sayuran segar',
                'images' => [
                    'product-galleries/pexels-meggy-kadam-aryanto-3797063-7429247.jpg',
                    'product-galleries/pexels-meggy-kadam-aryanto-3797063-9620137.jpg',
                    'product-galleries/pexels-meggy-kadam-aryanto-3797063-11346027.jpg'
                ]
            ],
            [
                'name' => 'Ayam Goreng Kremes',
                'price' => 30000,
                'description' => 'Ayam goreng dengan bumbu kremes renyah',
                'images' => [
                    'product-galleries/pexels-ffawdyy-13294514.jpgX',
                    'product-galleries/pexels-ffawdyy-13294534.jpg',
                    'product-galleries/pexels-ffawdyy-13294536.jpg'
                ]
            ]
        ],
        'Minuman' => [
            [
                'name' => 'Es Teh Manis',
                'price' => 5000,
                'description' => 'Teh manis dingin segar',
                'images' => [
                    'product-galleries/pexels-pixabay-372851.jpg',
                    'product-galleries/pexels-polina-tankilevitch-4110012.jpg',
                    'product-galleries/pexels-pragyanbezbo-2010701.jpg'
                ]
            ],
            [
                'name' => 'Es Jeruk',
                'price' => 7000,
                'description' => 'Jeruk peras segar dengan es',
                'images' => [
                    'product-galleries/pexels-tijana-drndarski-449691-3338681.jpg',
                    'product-galleries/pexels-tijana-drndarski-449691-3656119.jpg',
                    'product-galleries/pexels-pixabay-279906.jpg'
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

                // Store just the path without bucket name
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

            // Get and verify the S3 URL (will include bucket name)
            $url = Storage::disk('s3')->url($s3Path);
            $this->command->info("Successfully uploaded {$localPath} to S3");
            $this->command->info("File accessible at: {$url}");

            // Return the path without bucket name for database storage

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
            $profilePhotoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
            $s3ProfilePhotoPath = 'profile-photos/merchant-' . $merchantUser->id . '.jpg';

            if ($this->uploadFileToS3($profilePhotoPath, $s3ProfilePhotoPath)) {
                $merchantUser->forceFill([
                    'profile_photo_path' => $s3ProfilePhotoPath
                ])->save();
            }

            // Create merchant first
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
            ]);

            // Upload merchant logo after merchant creation to get correct ID
            $localLogoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
            $s3LogoPath = 'merchants/logos/merchant-' . $merchant->id . '.jpg';
            if ($this->uploadFileToS3($localLogoPath, $s3LogoPath)) {
                $merchant->update(['logo' => $s3LogoPath]);
            }

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

            // Create Courier Account with profile photo
            $courierUser = User::create([
                'name' => 'Test Courier',
                'email' => 'courier@test.com',
                'password' => Hash::make('aswar123'),
                'phone_number' => '081234567892',
                'roles' => 'COURIER',
                'username' => 'testcourier',
                'is_active' => true
            ]);

            // Upload courier profile photo
            $courierProfilePhotoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
            $s3CourierProfilePath = 'profile-photos/courier-' . $courierUser->id . '.jpg';
            if ($this->uploadFileToS3($courierProfilePhotoPath, $s3CourierProfilePath)) {
                $courierUser->forceFill([
                    'profile_photo_path' => $s3CourierProfilePath
                ])->save();
            }

            // Create Regular User Account with profile photo
            $user = User::create([
                'name' => 'Test User',
                'email' => 'user@test.com',
                'password' => Hash::make('aswar123'),
                'phone_number' => '081234567893',
                'roles' => 'USER',
                'username' => 'testuser',
                'is_active' => true
            ]);

            // Upload user profile photo
            $userProfilePhotoPath = 'profile-photos/01JBE54Q492SD1AC9SA6NNWF6D.jpg';
            $s3UserProfilePath = 'profile-photos/user-' . $user->id . '.jpg';
            if ($this->uploadFileToS3($userProfilePhotoPath, $s3UserProfilePath)) {
                $user->forceFill([
                    'profile_photo_path' => $s3UserProfilePath
                ])->save();
            }

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
                        $localImagePath = $image;
                        $s3ImagePath = 'products/images/' . $product->id . '-' . $index . '-' . basename($image);

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

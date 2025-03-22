<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AntarKanMaCourierSeeder extends Seeder
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

            // Create AntarKanMa Courier Account
            $courierUser = User::create([
                'name' => 'AntarKanMa',
                'email' => 'antarkanma@courier.com',
                'password' => Hash::make('antarkanma123'),
                'phone_number' => '081234567899',
                'roles' => 'COURIER',
                'username' => 'antarkanma',
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

            // Create Courier record
            Courier::create([
                'user_id' => $courierUser->id,
                'vehicle_type' => 'Motor',
                'license_plate' => 'DD 1234 XX',
                'wallet_balance' => 100000.00, // Initial balance
                'fee_per_order' => 2000.00,    // Fee per delivery
                'is_wallet_active' => true,
                'minimum_balance' => 10000.00
            ]);

            DB::commit();
            $this->command->info('AntarKanMa courier account created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Seeding failed", ['error' => $e->getMessage()]);
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}

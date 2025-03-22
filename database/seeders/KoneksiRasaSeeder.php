<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Merchant;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KoneksiRasaSeeder extends Seeder
{
    private function copyFile($sourcePath, $destinationPath)
    {
        try {
            $sourcePath = base_path('public/' . $sourcePath);
            $destinationPath = base_path('public/' . $destinationPath);

            if (!file_exists($sourcePath)) {
                $this->command->error("Source file not found: {$sourcePath}");
                return false;
            }

            // Create destination directory if it doesn't exist
            $destinationDir = dirname($destinationPath);
            if (!file_exists($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            // Copy the file
            if (copy($sourcePath, $destinationPath)) {
                $this->command->info("Successfully copied file to: {$destinationPath}");
                return true;
            } else {
                $this->command->error("Failed to copy file to: {$destinationPath}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to copy file", [
                'sourcePath' => $sourcePath,
                'destinationPath' => $destinationPath,
                'error' => $e->getMessage()
            ]);
            $this->command->error("Failed to copy file: " . $e->getMessage());
            return false;
        }
    }

    public function run()
    {
        DB::beginTransaction();

        try {
            // Create Merchant Account
            $merchantUser = User::create([
                'name' => 'Koneksi Rasa',
                'email' => 'koneksirasa@test.com',
                'password' => Hash::make('aswar123'),
                'phone_number' => '087812379186',
                'roles' => 'MERCHANT',
                'username' => 'koneksirasa',
                'is_active' => true
            ]);

            // Copy merchant profile photo
            $profilePhotoPath = 'images/Logo Koneksi Rasa.png';
            $destinationProfilePath = 'profile-photos/merchant-' . $merchantUser->id . '.png';

            if ($this->copyFile($profilePhotoPath, $destinationProfilePath)) {
                $merchantUser->forceFill([
                    'profile_photo_path' => $destinationProfilePath
                ])->save();
            }

            // Create merchant
            $merchant = Merchant::create([
                'owner_id' => $merchantUser->id,
                'name' => 'Koneksi Rasa',
                'address' => 'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',
                'phone_number' => '087812379186',
                'status' => 'active',
                'latitude' => -4.647021,
                'longitude' => 119.585073,
                'description' => 'Koneksi Rasa - Tempat makan dengan cita rasa yang menghubungkan',
                'opening_time' => Carbon::createFromTime(8, 0, 0),
                'closing_time' => Carbon::createFromTime(22, 0, 0),
                'operating_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            ]);

            // Store merchant logo in S3
            $localLogoPath = public_path('images/Logo Koneksi Rasa.png');
            $s3Path = 'merchants/logos/merchant-' . $merchant->id . '.png';

            if (file_exists($localLogoPath)) {
                try {
                    $fileContents = file_get_contents($localLogoPath);
                    Storage::disk('s3')->put($s3Path, $fileContents, 'public');
                    $merchant->update(['logo' => $s3Path]);
                    $this->command->info("Successfully uploaded logo to S3");
                } catch (\Exception $e) {
                    Log::error("Failed to upload logo to S3", ['error' => $e->getMessage()]);
                    $this->command->error("Failed to upload logo to S3: " . $e->getMessage());
                }
            } else {
                $this->command->error("Logo file not found at: " . $localLogoPath);
            }

            // Create merchant location
            UserLocation::create([
                'user_id' => $merchantUser->id,
                'address' => 'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',
                'district' => 'Segeri',
                'city' => 'Pangkajene Dan Kepulauan',
                'postal_code' => '90655',
                'latitude' => -4.647021,
                'longitude' => 119.585073,
                'address_type' => 'Toko',
                'phone_number' => '087812379186',
                'is_default' => true,
                'is_active' => true,
                'country' => 'Indonesia'
            ]);

            DB::commit();
            $this->command->info('Koneksi Rasa merchant seeded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Seeding failed", ['error' => $e->getMessage()]);
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}

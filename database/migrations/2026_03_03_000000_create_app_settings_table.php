<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, image, boolean, json
            $table->string('group')->default('general'); // general, payment, branding, etc
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaultSettings = [
            [
                'key' => 'qris_image',
                'value' => null,
                'type' => 'image',
                'group' => 'payment',
                'description' => 'QRIS image for payment',
            ],
            [
                'key' => 'bank_name',
                'value' => 'BCA',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Bank name for manual transfer',
            ],
            [
                'key' => 'bank_account_number',
                'value' => '1234567890',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Bank account number for manual transfer',
            ],
            [
                'key' => 'bank_account_name',
                'value' => 'PT Antarkanma Indonesia',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Bank account name for manual transfer',
            ],
            [
                'key' => 'app_name',
                'value' => 'AntarkanMa',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Application name',
            ],
            [
                'key' => 'app_logo',
                'value' => 'images/logo-customer.svg',
                'type' => 'image',
                'group' => 'branding',
                'description' => 'Application logo',
            ],
            [
                'key' => 'customer_service_phone',
                'value' => null,
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Customer service phone number',
            ],
            [
                'key' => 'customer_service_email',
                'value' => 'support@antarkanma.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Customer service email',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('app_settings')->insert($setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

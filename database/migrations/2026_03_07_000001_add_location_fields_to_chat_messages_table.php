<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds location fields for share location feature with high accuracy GPS support
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Location fields for share location feature
            $table->decimal('latitude', 10, 8)->nullable()->after('attachment_url')
                  ->comment('GPS latitude coordinate (-90 to 90)');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')
                  ->comment('GPS longitude coordinate (-180 to 180)');
            $table->decimal('location_accuracy', 5, 2)->nullable()->after('longitude')
                  ->comment('GPS accuracy in meters (e.g., 5.50 means ±5.5m accuracy)');
            $table->string('location_address')->nullable()->after('location_accuracy')
                  ->comment('Human-readable address for the location');
            $table->string('location_name')->nullable()->after('location_address')
                  ->comment('Location name/label (e.g., "Kantor", "Rumah")');
            
            // Update type enum to include LOCATION
            DB::statement("ALTER TABLE chat_messages MODIFY COLUMN type ENUM('TEXT', 'IMAGE', 'FILE', 'LOCATION') DEFAULT 'TEXT'");
            
            // Add index for location-based queries
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['latitude', 'longitude']);
            
            // Drop location columns
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_accuracy',
                'location_address',
                'location_name',
            ]);
            
            // Revert type enum
            DB::statement("ALTER TABLE chat_messages MODIFY COLUMN type ENUM('TEXT', 'IMAGE', 'FILE') DEFAULT 'TEXT'");
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add service fee fields to transactions table for transparent fee model.
     * Service Fee: Rp 500 per TRANSACTION (bukan per order!)
     * Updated: 12 Maret 2026 - Changed from per order to per transaction
     * Platform Fee: 10% of base_shipping_price
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Base shipping price (before service fee)
            $table->decimal('base_shipping_price', 10, 2)->default(0)->after('shipping_price');
            
            // Service fee (fixed Rp 500, transparent to customer)
            $table->decimal('service_fee', 10, 2)->default(500)->after('base_shipping_price');
            
            // Platform fee (10% of base_shipping_price)
            $table->decimal('platform_fee', 10, 2)->default(0)->after('service_fee');
            
            // Update existing shipping_price to include service fee
            // This maintains backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['base_shipping_price', 'service_fee', 'platform_fee']);
        });
    }
};

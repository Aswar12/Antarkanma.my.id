<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_manual_order')->default(false);
            $table->string('manual_merchant_name')->nullable();
            $table->text('manual_merchant_address')->nullable();
            $table->string('manual_merchant_phone')->nullable();
            $table->foreignId('merchant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_manual_order', 'manual_merchant_name', 'manual_merchant_address', 'manual_merchant_phone']);
            $table->foreignId('merchant_id')->nullable(false)->change();
        });
    }
};

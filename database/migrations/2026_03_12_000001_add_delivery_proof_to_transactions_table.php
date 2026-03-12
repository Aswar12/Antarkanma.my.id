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
        Schema::table('transactions', function (Blueprint $table) {
            // Add delivery proof fields for courier completion evidence
            $table->string('delivery_proof_image')->nullable()->after('platform_payment_verified')
                  ->comment('Path to delivery proof photo taken by courier');
            $table->timestamp('delivery_proof_at')->nullable()->after('delivery_proof_image')
                  ->comment('Timestamp when delivery proof was captured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['delivery_proof_image', 'delivery_proof_at']);
        });
    }
};

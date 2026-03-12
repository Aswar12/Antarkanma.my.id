<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add merchant payment confirmation fields for fraud prevention.
     * Merchant must confirm they received the money before order is processed.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Merchant payment verification flag
            $table->boolean('merchant_payment_verified')->default(false)->after('merchant_payment_proof');
            
            // Platform payment verification flag (auto-verified)
            $table->boolean('platform_payment_verified')->default(false)->after('platform_payment_proof');
            
            // Manual review flag (for first-time users or suspicious activity)
            $table->boolean('requires_manual_review')->default(false)->after('platform_payment_verified');
            
            // Admin verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            
            // Add PENDING_VERIFICATION to payment_status enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('PENDING', 'PARTIAL_PAID', 'PAID', 'PENDING_VERIFICATION', 'COMPLETED', 'FAILED') DEFAULT 'PENDING'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert payment_status enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('PENDING', 'PARTIAL_PAID', 'PAID', 'COMPLETED', 'FAILED') DEFAULT 'PENDING'");
            
            $table->dropColumn([
                'merchant_payment_verified',
                'platform_payment_verified',
                'requires_manual_review',
                'verified_by',
                'verified_at',
            ]);
        });
    }
};

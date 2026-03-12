<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add dual QRIS payment support to transactions table.
     * This allows customer to pay separately:
     * - Merchant QRIS: For food/products
     * - Platform QRIS: For delivery fee + service fee
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Update payment_method enum to include QRIS options
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('COD', 'QRIS_DUAL', 'QRIS_PLATFORM', 'MANUAL', 'ONLINE') DEFAULT 'COD'");
            
            // Update payment_status enum to include PARTIAL_PAID
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('PENDING', 'PARTIAL_PAID', 'PAID', 'COMPLETED', 'FAILED') DEFAULT 'PENDING'");
            
            // Amount split for dual QRIS payment
            $table->decimal('merchant_amount', 10, 2)->default(0)->after('total_price');
            $table->decimal('platform_amount', 10, 2)->default(0)->after('merchant_amount');
            
            // Grand total (merchant_amount + platform_amount)
            $table->decimal('grand_total', 10, 2)->default(0)->after('platform_amount');
            
            // QRIS URLs for dual payment
            $table->string('merchant_qris_url')->nullable()->after('grand_total');
            $table->string('platform_qris_url')->nullable()->after('merchant_qris_url');
            
            // Payment timestamps for dual QRIS
            $table->timestamp('merchant_paid_at')->nullable()->after('platform_qris_url');
            $table->timestamp('platform_paid_at')->nullable()->after('merchant_paid_at');
            
            // Payment proof uploads
            $table->string('merchant_payment_proof')->nullable()->after('platform_paid_at');
            $table->string('platform_payment_proof')->nullable()->after('merchant_payment_proof');
            
            // Courier payout tracking (for online payments)
            $table->enum('courier_payout_status', [
                'PENDING',
                'CREDITED',
                'WITHDRAWN',
                'FAILED',
            ])->default('PENDING')->after('platform_payment_proof');
            
            $table->timestamp('courier_paid_at')->nullable()->after('courier_payout_status');
            
            // Add courier_earning if not exists (for online payment wallet credit)
            if (!Schema::hasColumn('transactions', 'courier_earning')) {
                $table->decimal('courier_earning', 10, 2)->default(0)->after('platform_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert payment_method enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('MANUAL', 'ONLINE') DEFAULT 'MANUAL'");
            
            // Revert payment_status enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('PENDING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING'");
            
            $table->dropColumn([
                'merchant_amount',
                'platform_amount',
                'grand_total',
                'merchant_qris_url',
                'platform_qris_url',
                'merchant_paid_at',
                'platform_paid_at',
                'merchant_payment_proof',
                'platform_payment_proof',
                'courier_payout_status',
                'courier_paid_at',
            ]);
            
            if (Schema::hasColumn('transactions', 'courier_earning')) {
                $table->dropColumn('courier_earning');
            }
        });
    }
};

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
        Schema::table('couriers', function (Blueprint $table) {
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->decimal('fee_per_order', 10, 2)->default(2000);
            $table->boolean('is_wallet_active')->default(true);
            $table->decimal('minimum_balance', 10, 2)->default(10000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_balance',
                'fee_per_order',
                'is_wallet_active',
                'minimum_balance'
            ]);
        });
    }
};

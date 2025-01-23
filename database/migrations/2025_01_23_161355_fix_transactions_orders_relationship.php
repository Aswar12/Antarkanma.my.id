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
        // First, drop order_id from transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });

        // Then, add transaction_id to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('transaction_id')->after('id')->constrained('transactions');
            $table->foreignId('merchant_id')->after('user_id')->constrained('merchants');
            $table->enum('merchant_approval', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->after('order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove transaction_id from orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
            $table->dropColumn('merchant_approval');
        });

        // Add back order_id to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('order_id')->after('id');
        });
    }
};

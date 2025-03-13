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
        Schema::table('order_items', function (Blueprint $table) {
            $table->text('customer_note')->nullable()->after('price');
        });

        // Remove customer_note from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_note')->nullable()->after('merchant_approval');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('customer_note');
        });
    }
};

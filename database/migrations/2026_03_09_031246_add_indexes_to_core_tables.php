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
        $indexes = [
            ['products', 'merchant_id'],
            ['products', 'category_id'],
            ['products', 'status'],
            ['orders', 'user_id'],
            ['orders', 'merchant_id'],
            ['orders', 'status'],
            ['transactions', 'order_id'],
            ['transactions', 'courier_id'],
            ['transactions', 'status'],
            ['transactions', 'transaction_type'],
            ['chat_messages', 'chat_id'],
            ['chat_messages', 'sender_id'],
        ];

        foreach ($indexes as [$table, $column]) {
            try {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['merchant_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['merchant_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['courier_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['transaction_type']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['chat_id']);
            $table->dropIndex(['sender_id']);
        });
    }
};

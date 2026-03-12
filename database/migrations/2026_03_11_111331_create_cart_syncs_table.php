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
        Schema::create('cart_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->boolean('is_selected')->default(true);
            $table->dateTime('last_added_at');
            $table->dateTime('last_checkout_attempt_at')->nullable();
            $table->boolean('has_checked_out')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'merchant_id']);
            $table->index(['user_id', 'is_selected']);
            $table->index(['has_checked_out', 'last_added_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_syncs');
    }
};

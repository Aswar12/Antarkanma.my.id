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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_location_id')->constrained('user_locations');
            $table->decimal('total_price', 10, 2);
            $table->decimal('shipping_price', 10, 2);
            $table->dateTime('payment_date')->nullable();
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELED']);
            $table->enum('payment_method', ['MANUAL', 'ONLINE']);
            $table->enum('payment_status', ['PENDING', 'COMPLETED', 'FAILED']);
            $table->integer('rating')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

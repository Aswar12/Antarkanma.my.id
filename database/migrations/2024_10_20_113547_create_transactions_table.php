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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('address');
            $table->decimal('total_price', 10, 2);
            $table->decimal('shipping_price', 8, 2);
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELED'])->default('PENDING');
            $table->enum('payment', ['MANUAL', 'ONLINE']);
            $table->enum('payment_status', ['PENDING', 'COMPLETED', 'FAILED'])->default('PENDING');
            $table->foreignId('user_location_id')->onDelete('cascade');
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->onDelete('set null');
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

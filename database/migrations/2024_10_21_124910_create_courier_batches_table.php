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
        Schema::create('courier_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('couriers');
            $table->enum('status', ['PREPARING', 'IN_PROGRESS', 'COMPLETED']);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_batches');
    }
};

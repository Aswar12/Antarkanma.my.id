<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('table_number');
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->enum('status', ['AVAILABLE', 'OCCUPIED', 'RESERVED'])->default('AVAILABLE');
            $table->foreignId('current_pos_transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['merchant_id', 'table_number']);
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_tables');
    }
};

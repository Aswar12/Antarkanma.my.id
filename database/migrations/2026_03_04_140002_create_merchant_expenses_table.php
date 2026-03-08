<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['BAHAN_BAKU', 'OPERASIONAL', 'GAJI', 'SEWA', 'UTILITAS', 'LAINNYA']);
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->date('expense_date');
            $table->string('receipt_image')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'expense_date']);
            $table->index(['merchant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_expenses');
    }
};

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
        Schema::create('service_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('service_fee', 10, 2)->default(500)->comment('Service fee per transaksi in Rupiah (sekali per transaksi, bukan per order)');
            $table->boolean('is_active')->default(true)->comment('Whether service fee is active');
            $table->string('updated_by')->nullable()->comment('Admin who last updated the fee');
            $table->text('notes')->nullable()->comment('Notes about the fee change');
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_fee_settings');
    }
};

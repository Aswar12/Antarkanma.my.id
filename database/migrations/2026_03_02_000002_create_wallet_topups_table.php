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
        Schema::create('wallet_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('couriers')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Nominal topup (tanpa unique code)
            $table->unsignedInteger('unique_code'); // 3 digit unique code
            $table->decimal('transfer_amount', 10, 2); // amount + unique_code
            $table->string('payment_proof')->nullable(); // Path file bukti transfer
            $table->enum('status', ['PENDING', 'VERIFIED', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->boolean('bank_notification_matched')->default(false);
            $table->text('admin_note')->nullable(); // Alasan reject/note
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa
            $table->index('courier_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_topups');
    }
};

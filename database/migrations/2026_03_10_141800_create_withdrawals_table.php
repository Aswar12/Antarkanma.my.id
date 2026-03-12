<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create withdrawals table for courier withdrawal system with admin approval.
     * Withdrawal Fee: Rp 1.000 (fixed)
     * Minimum Withdraw: Rp 50.000
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            
            // Courier reference
            $table->foreignId('courier_id')->constrained()->onDelete('cascade');
            
            // Amount fields
            $table->decimal('amount', 10, 2); // Amount requested by courier
            $table->decimal('fee', 10, 2)->default(1000); // Fixed Rp 1.000 withdrawal fee
            $table->decimal('total_amount', 10, 2); // amount - fee (amount courier receives)
            
            // Status tracking
            $table->enum('status', [
                'PENDING',      // Menunggu approval admin
                'APPROVED',     // Approved, siap transfer
                'PROCESSING',   // Sedang ditransfer
                'COMPLETED',    // Selesai
                'REJECTED'      // Ditolak
            ])->default('PENDING');
            
            // Bank account details
            $table->string('bank_account_name');
            $table->string('bank_account_number');
            $table->string('bank_name');
            
            // Admin tracking
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['courier_id', 'status']);
            $table->index('created_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

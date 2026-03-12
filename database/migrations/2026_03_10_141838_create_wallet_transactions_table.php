<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create wallet_transactions table for tracking all courier wallet movements.
     * Types: TOPUP, WITHDRAWAL, FEE, EARNING
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            
            // Courier reference
            $table->foreignId('courier_id')->constrained()->onDelete('cascade');
            
            // Transaction type
            $table->enum('type', [
                'TOPUP',      // Topup saldo
                'WITHDRAWAL', // Penarikan dana
                'FEE',        // Potongan fee (platform fee, service fee)
                'EARNING',    // Penghasilan dari delivery
            ]);
            
            // Amount (positive for income, negative for deduction)
            $table->decimal('amount', 10, 2);
            
            // Balance tracking
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            
            // Reference to related entity (order, withdrawal, topup, etc.)
            $table->foreignId('reference_id')->nullable(); // withdrawal_id, order_id, topup_id
            $table->string('reference_type')->nullable(); // Withdrawal, Order, WalletTopup
            
            // Description
            $table->text('description');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['courier_id', 'type']);
            $table->index('created_at');
            $table->index(['courier_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

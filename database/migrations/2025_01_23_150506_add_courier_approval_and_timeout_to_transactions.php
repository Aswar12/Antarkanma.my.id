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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('courier_approval', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->after('payment_status');
            $table->timestamp('timeout_at')->nullable()->after('courier_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('courier_approval');
            $table->dropColumn('timeout_at');
        });
    }
};

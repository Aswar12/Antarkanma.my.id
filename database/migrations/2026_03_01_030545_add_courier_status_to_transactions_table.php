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
            // Add courier_status column after courier_approval
            if (!Schema::hasColumn('transactions', 'courier_status')) {
                $table->string('courier_status')->default('IDLE')->after('courier_approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop courier_status column if exists
            if (Schema::hasColumn('transactions', 'courier_status')) {
                $table->dropColumn('courier_status');
            }
        });
    }
};

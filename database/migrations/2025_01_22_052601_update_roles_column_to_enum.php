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
        Schema::table('users', function (Blueprint $table) {
            // First drop the existing roles column if it exists
            if (Schema::hasColumn('users', 'roles')) {
                $table->dropColumn('roles');
            }
            
            // Add the roles column as enum
            $table->enum('roles', ['ADMIN', 'USER', 'MERCHANT', 'COURIER'])->default('USER');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Convert back to string column
            $table->string('roles')->default('USER')->change();
        });
    }
};

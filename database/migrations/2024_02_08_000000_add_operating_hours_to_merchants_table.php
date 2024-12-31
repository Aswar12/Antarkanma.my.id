<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('operating_days')->nullable(); // Will store days as comma-separated values e.g. "1,2,3,4,5" for Mon-Fri
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['opening_time', 'closing_time', 'operating_days']);
        });
    }
};

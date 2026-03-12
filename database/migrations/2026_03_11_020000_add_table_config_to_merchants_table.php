<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->enum('payment_flow', ['PAY_FIRST', 'PAY_LAST'])
                  ->default('PAY_FIRST')
                  ->after('is_active')
                  ->comment('PAY_FIRST=bayar di awal (fast food), PAY_LAST=bayar di akhir (restaurant)');

            $table->boolean('auto_release_table')
                  ->default(true)
                  ->after('payment_flow')
                  ->comment('Auto-release meja setelah durasi habis');

            $table->unsignedSmallInteger('default_dine_duration')
                  ->default(60)
                  ->after('auto_release_table')
                  ->comment('Default durasi makan dalam menit (30-120)');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['payment_flow', 'auto_release_table', 'default_dine_duration']);
        });
    }
};

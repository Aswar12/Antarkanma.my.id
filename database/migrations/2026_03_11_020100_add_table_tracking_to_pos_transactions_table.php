<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->timestamp('food_completed_at')
                  ->nullable()
                  ->after('table_number')
                  ->comment('Waktu makanan selesai disiapkan/disajikan');

            $table->timestamp('auto_release_at')
                  ->nullable()
                  ->after('food_completed_at')
                  ->comment('Jadwal auto-release meja (food_completed_at + duration)');

            $table->timestamp('table_released_at')
                  ->nullable()
                  ->after('auto_release_at')
                  ->comment('Waktu meja benar-benar di-release');

            $table->foreignId('released_by')
                  ->nullable()
                  ->after('table_released_at')
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('User yang release meja (null jika auto-release)');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropForeign(['released_by']);
            $table->dropColumn([
                'food_completed_at',
                'auto_release_at',
                'table_released_at',
                'released_by',
            ]);
        });
    }
};

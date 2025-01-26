<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah kolom address_type sudah ada
        if (!Schema::hasColumn('user_locations', 'address_type')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->enum('address_type', ['RUMAH', 'KANTOR', 'TOKO', 'LAINNYA'])
                    ->default('RUMAH')
                    ->after('longitude');
            });
        } else {
            // Jika kolom sudah ada, ubah tipe enum-nya
            DB::statement("ALTER TABLE user_locations MODIFY COLUMN address_type ENUM('RUMAH', 'KANTOR', 'TOKO', 'LAINNYA') DEFAULT 'RUMAH'");
        }

        // Tambahkan kolom-kolom baru jika belum ada
        Schema::table('user_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('user_locations', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('user_locations', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('user_locations', 'phone_number')) {
                $table->string('phone_number')->after('address_type');
            }
            if (!Schema::hasColumn('user_locations', 'notes')) {
                $table->text('notes')->nullable()->after('is_default');
            }
            if (!Schema::hasColumn('user_locations', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
            if (!Schema::hasColumn('user_locations', 'deleted_at')) {
                $table->softDeletes();
            }

            // Ubah tipe data address menjadi text jika belum
            $table->text('address')->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            // Hapus kolom hanya jika mereka ada
            $columns = [
                'customer_name',
                'district',
                'address_type',
                'phone_number',
                'notes',
                'is_active',
                'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('user_locations', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Kembalikan tipe data address ke string jika diperlukan
            if (Schema::hasColumn('user_locations', 'address')) {
                $table->string('address')->change();
            }
        });
    }
};

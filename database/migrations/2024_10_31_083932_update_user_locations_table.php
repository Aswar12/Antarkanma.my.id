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
        Schema::table('user_locations', function (Blueprint $table) {
            // Menambah kolom baru
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('district')->nullable()->after('city');
            $table->enum('address_type', ['RUMAH', 'KANTOR', 'TOKO', 'LAINNYA'])->default('RUMAH')->after('longitude');
            $table->string('phone_number')->after('address_type');
            $table->text('notes')->nullable()->after('is_default');
            $table->boolean('is_active')->default(true)->after('notes');
            $table->softDeletes();

            // Mengubah tipe data kolom yang sudah ada
            $table->text('address')->change();

            // Menambah index
            $table->index('user_id');
            $table->index('is_default');
            $table->index('address_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            // Menghapus index dengan pengecekan
            if (Schema::hasIndex('user_locations', 'user_locations_user_id_index')) {
                $table->dropIndex('user_locations_user_id_index');
            }
            if (Schema::hasIndex('user_locations', 'user_locations_is_default_index')) {
                $table->dropIndex('user_locations_is_default_index');
            }
            if (Schema::hasIndex('user_locations', 'user_locations_address_type_index')) {
                $table->dropIndex('user_locations_address_type_index');
            }

            // Menghapus kolom yang ditambahkan
            $table->dropColumn([
                'customer_name',
                'district',
                'address_type',
                'phone_number',
                'notes',
                'is_active',
                'deleted_at'
            ]);

            // Mengembalikan tipe data address ke string
            $table->string('address')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dapatkan status enum yang ada saat ini
        $currentStatuses = "'" . implode("','", [
            'PENDING',
            'PROCESSING',
            'READY_FOR_PICKUP',
            'COMPLETED',
            'CANCELED'
        ]) . "'";

        // Tambahkan status baru
        $newStatuses = "'" . implode("','", [
            'PENDING',
            'WAITING_APPROVAL',
            'PROCESSING',
            'READY_FOR_PICKUP',
            'PICKED_UP',
            'COMPLETED',
            'CANCELED'
        ]) . "'";

        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM($newStatuses) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke status enum sebelumnya
        $oldStatuses = "'" . implode("','", [
            'PENDING',
            'PROCESSING',
            'READY_FOR_PICKUP',
            'COMPLETED',
            'CANCELED'
        ]) . "'";

        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM($oldStatuses) NOT NULL");
    }
};

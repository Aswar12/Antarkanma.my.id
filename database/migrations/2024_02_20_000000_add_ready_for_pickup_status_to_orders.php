<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop and recreate the order_status enum with the new status
            $table->dropColumn('order_status');
            $table->enum('order_status', ['PENDING', 'PROCESSING', 'READY_FOR_PICKUP', 'COMPLETED', 'CANCELED'])->after('total_amount');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert back to original status options
            $table->dropColumn('order_status');
            $table->enum('order_status', ['PENDING', 'PROCESSING', 'COMPLETED', 'CANCELED'])->after('total_amount');
        });
    }
};

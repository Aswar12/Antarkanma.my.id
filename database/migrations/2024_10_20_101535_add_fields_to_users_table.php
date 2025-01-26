<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roles', ['USER', 'MERCHANT', 'COURIER'])->default('USER')->after('password');
            $table->string('username')->nullable()->unique()->after('roles');
            $table->string('phone_number')->nullable()->after('username');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['roles', 'username', 'phone_number']);
        });
    }
};

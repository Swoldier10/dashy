<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('salutation', 8)->nullable()->after('id');
            $table->string('first_name', 80)->nullable()->after('salutation');
            $table->string('last_name', 80)->nullable()->after('first_name');
            $table->string('google_id')->nullable()->unique()->after('remember_token');
            $table->string('avatar', 2048)->nullable()->after('google_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['salutation', 'first_name', 'last_name', 'google_id', 'avatar']);
        });
    }
};

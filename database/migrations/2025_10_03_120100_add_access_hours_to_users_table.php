<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_start_hour')->nullable()->after('remember_token');
            $table->unsignedTinyInteger('access_end_hour')->nullable()->after('access_start_hour');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('access_end_hour');
            $table->dropColumn('access_start_hour');
        });
    }
};
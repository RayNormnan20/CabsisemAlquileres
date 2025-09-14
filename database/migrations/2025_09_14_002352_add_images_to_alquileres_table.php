<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->string('imagen_1_path', 500)->nullable()->after('contrato_path');
            $table->string('imagen_2_path', 500)->nullable()->after('imagen_1_path');
            $table->string('imagen_3_path', 500)->nullable()->after('imagen_2_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn(['imagen_1_path', 'imagen_2_path', 'imagen_3_path']);
        });
    }
};

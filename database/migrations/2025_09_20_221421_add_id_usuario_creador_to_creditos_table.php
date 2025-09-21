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
        Schema::table('creditos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario_creador')->nullable()->after('id_ruta');
            $table->foreign('id_usuario_creador')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_creador']);
            $table->dropColumn('id_usuario_creador');
        });
    }
};

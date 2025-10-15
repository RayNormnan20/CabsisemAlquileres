<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Última ruta seleccionada por el usuario (persistencia entre sesiones)
            $table->unsignedBigInteger('last_selected_ruta_id')->nullable();
            $table->foreign('last_selected_ruta_id')
                ->references('id_ruta')
                ->on('ruta')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_selected_ruta_id']);
            $table->dropColumn('last_selected_ruta_id');
        });
    }
};
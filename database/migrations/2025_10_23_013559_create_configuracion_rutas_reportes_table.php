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
        Schema::create('configuracion_rutas_reportes', function (Blueprint $table) {
            $table->id();
            $table->string('modulo')->unique(); // Nombre del módulo (ej: 'ReportesCristian')
            $table->json('rutas_permitidas'); // Array JSON con las rutas que pueden acceder
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion_rutas_reportes');
    }
};

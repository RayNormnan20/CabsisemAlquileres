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
        Schema::create('clientes_alquiler', function (Blueprint $table) {
            $table->id('id_cliente_alquiler');

            // Información personal
            $table->unsignedBigInteger('id_tipo_documento');
            $table->string('numero_documento', 20);
            $table->string('nombre', 100);
            $table->string('apellido', 100);

            // Información de contacto
            $table->string('celular', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255);
            $table->string('direccion2', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('nombre_negocio', 100)->nullable();

            // Relaciones y control
            $table->unsignedBigInteger('id_ruta');
            $table->unsignedBigInteger('id_usuario_creador');
            $table->boolean('activo')->default(true);

            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas
            $table->index(['id_ruta', 'activo']);
            $table->index('numero_documento');
            $table->index('nombre');
            $table->index('apellido');

            // Claves foráneas
            $table->foreign('id_tipo_documento')->references('id_tipo_documento')->on('tipo_documento');
            $table->foreign('id_ruta')->references('id_ruta')->on('ruta');
            $table->foreign('id_usuario_creador')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes_alquiler');
    }
};

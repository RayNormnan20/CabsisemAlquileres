<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('edificios', function (Blueprint $table) {
            $table->id('id_edificio');
            $table->string('nombre', 100);
            $table->string('direccion', 255);
            $table->string('ciudad', 100)->nullable();
            $table->integer('numero_pisos')->default(1);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_cliente_alquiler')->nullable(); // Propietario (puede ser null)
            $table->unsignedBigInteger('id_ruta')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_usuario_creador');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['id_cliente_alquiler', 'activo']);
            $table->index('nombre');
            $table->index('id_ruta');

            // Claves foráneas
            $table->foreign('id_cliente_alquiler')
                  ->references('id_cliente_alquiler')
                  ->on('clientes_alquiler')
                  ->onDelete('set null'); // Cambiar a set null en lugar de cascade
            $table->foreign('id_ruta')
                  ->references('id_ruta')
                  ->on('ruta')
                  ->onDelete('set null');
            $table->foreign('id_usuario_creador')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('edificios');
    }
};

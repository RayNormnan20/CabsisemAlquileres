<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->unsignedBigInteger('id_edificio');
            $table->string('numero_departamento', 20);
            $table->integer('piso')->default(1);
            $table->integer('cuartos');
            $table->integer('banos');
            $table->decimal('metros_cuadrados', 8, 2)->nullable();
            $table->decimal('precio_alquiler', 10, 2);
            $table->text('descripcion')->nullable();
            $table->string('foto_path', 500)->nullable();
            $table->unsignedBigInteger('id_estado_departamento');
            $table->unsignedBigInteger('id_ruta')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_usuario_creador');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['id_edificio', 'activo']);
            $table->index('id_estado_departamento');
            $table->index('precio_alquiler');
            $table->index('id_ruta');
            $table->unique(['id_edificio', 'numero_departamento']);

            // Claves foráneas
            $table->foreign('id_edificio')
                  ->references('id_edificio')
                  ->on('edificios')
                  ->onDelete('cascade');
            $table->foreign('id_estado_departamento')
                  ->references('id_estado_departamento')
                  ->on('estados_departamento')
                  ->onDelete('restrict');
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
        Schema::dropIfExists('departamentos');
    }
};

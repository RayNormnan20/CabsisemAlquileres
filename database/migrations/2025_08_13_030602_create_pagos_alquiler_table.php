<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pagos_alquiler', function (Blueprint $table) {
            $table->id('id_pago_alquiler');
            $table->unsignedBigInteger('id_alquiler');
            $table->date('fecha_pago');
            $table->decimal('monto_pagado', 10, 2);
            $table->integer('mes_correspondiente');
            $table->integer('ano_correspondiente');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta'])->default('efectivo');
            $table->string('referencia_pago', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('recibo_path', 500)->nullable();
            $table->unsignedBigInteger('id_usuario_registro');
            $table->timestamps();

            // Índices con nombres personalizados más cortos
            $table->index(['id_alquiler', 'ano_correspondiente', 'mes_correspondiente'], 'idx_pago_alquiler_periodo');
            $table->index('fecha_pago');
            $table->index(['ano_correspondiente', 'mes_correspondiente'], 'idx_periodo_pago');

            // Clave foránea
            $table->foreign('id_alquiler')
                  ->references('id_alquiler')
                  ->on('alquileres')
                  ->onDelete('cascade');
            $table->foreign('id_usuario_registro')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_alquiler');
    }
};

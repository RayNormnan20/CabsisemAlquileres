<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up()
    {
        Schema::table('creditos', function (Blueprint $table) {
            // Campos booleanos para gestión de cobranza
            $table->boolean('llamada_cliente')->default(false)
                  ->comment('Indica si se realizó llamada al cliente');
            $table->boolean('revisado')->default(false)
                  ->comment('Indica si fue revisado en campo');
            $table->boolean('analizado')->default(false)
                  ->comment('Indica si el crédito fue analizado');
            $table->boolean('por_renovar')->default(false)
                  ->comment('Indica si el crédito está marcado para renovación');
            $table->boolean('segundo_recorrido')->default(false)
                  ->comment('Indica si está en segundo recorrido de cobranza');

            // Campo para identificador del segundo cobrador
            $table->string('segundo_cobrador', 100)->nullable()
                  ->comment('Identificador del segundo cobrador asignado');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down()
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropColumn([
                'llamada_cliente',
                'revisado',
                'analizado',
                'por_renovar',
                'segundo_recorrido',
                'segundo_cobrador'
            ]);
        });
    }
};

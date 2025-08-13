<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id('id_alquiler');
            $table->unsignedBigInteger('id_departamento');
            $table->unsignedBigInteger('id_cliente_alquiler'); // Inquilino
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('precio_mensual', 10, 2);
            $table->decimal('deposito_garantia', 10, 2)->nullable();
            $table->date('fecha_proximo_pago');
            $table->integer('dia_pago')->default(1); // Día del mes
            $table->enum('estado_alquiler', ['activo', 'finalizado', 'suspendido'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->string('contrato_path', 500)->nullable();
            $table->unsignedBigInteger('id_usuario_creador');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['id_departamento', 'estado_alquiler']);
            $table->index(['id_cliente_alquiler', 'estado_alquiler']);
            $table->index('fecha_proximo_pago');
            $table->index('estado_alquiler');

            // Claves foráneas
            $table->foreign('id_departamento')
                  ->references('id_departamento')
                  ->on('departamentos')
                  ->onDelete('cascade');
            $table->foreign('id_cliente_alquiler')
                  ->references('id_cliente_alquiler')
                  ->on('clientes_alquiler')
                  ->onDelete('cascade');
            $table->foreign('id_usuario_creador')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('alquileres');
    }
};

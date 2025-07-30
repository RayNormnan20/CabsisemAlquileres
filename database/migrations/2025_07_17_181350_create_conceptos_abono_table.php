<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConceptosAbonoTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conceptos_abono', function (Blueprint $table) {
            $table->id('id_concepto_abono');

            $table->unsignedBigInteger('id_abono')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();

            $table->string('tipo_concepto');
            $table->decimal('monto', 10, 2);
            $table->string('foto_comprobante')->nullable();
            $table->string('referencia')->nullable(); // Campo para referencias y observaciones

            // Solo agregamos la columna, sin foreign key
            $table->unsignedBigInteger('id_caja')->nullable()->comment('Relación con cajas se agregará posteriormente');

            $table->timestamps();

            $table->foreign('id_abono')
                  ->references('id_abono')
                  ->on('abonos')
                  ->onDelete('cascade');

            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('users') // Asumiendo que tu tabla de usuarios se llama 'users'
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos_abono');
    }
}
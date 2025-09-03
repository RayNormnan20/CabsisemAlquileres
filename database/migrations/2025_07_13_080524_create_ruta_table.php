<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ruta', function (Blueprint $table) {
            $table->id('id_ruta');
            $table->string('nombre', 100);
            $table->unsignedBigInteger('id_oficina');
            $table->unsignedBigInteger('id_usuario')->nullable(); // Campo agregado para el vendedor
            $table->date('creada_en');
            $table->boolean('activa')->default(false);
            $table->unsignedBigInteger('id_tipo_documento')->nullable();
            $table->unsignedBigInteger('id_tipo_cobro')->nullable();
            $table->boolean('agregar_ceros_cantidades')->default(false)->comment('Agregar 3 ceros a las cantidades');
            $table->string('porcentajes_credito', 255)->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('id_oficina')
                  ->references('id_oficina')
                  ->on('oficina')
                  ->onDelete('restrict');

            $table->foreign('id_tipo_documento')
                  ->references('id_tipo_documento')
                  ->on('tipo_documento')
                  ->onDelete('set null');

            $table->foreign('id_tipo_cobro')
                  ->references('id_tipo_cobro')
                  ->on('tipo_cobro')
                  ->onDelete('set null');

            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('ruta', function (Blueprint $table) {
            $table->dropForeign(['id_oficina']);
            $table->dropForeign(['id_tipo_documento']);
            $table->dropForeign(['id_tipo_cobro']);
            $table->dropForeign(['id_usuario']);
        });

        Schema::dropIfExists('ruta');
    }
};
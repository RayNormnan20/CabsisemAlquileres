<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_yape_cliente')->nullable()->after('id_usuario');
            
            // Agregar la relación foránea
            $table->foreign('id_yape_cliente')->references('id')->on('yape_clientes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->dropForeign(['id_yape_cliente']);
            $table->dropColumn('id_yape_cliente');
        });
    }
};
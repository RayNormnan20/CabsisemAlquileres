<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->boolean('es_devolucion')->default(false)->after('activar_segundo_recorrido')
                ->comment('Indica si el registro es una devolución que no descuenta del crédito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->dropColumn('es_devolucion');
        });
    }
};
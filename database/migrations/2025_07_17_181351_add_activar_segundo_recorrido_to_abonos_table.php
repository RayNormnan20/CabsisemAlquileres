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
            $table->boolean('activar_segundo_recorrido')->default(false)->after('estado')
                ->comment('Checkbox para activar automáticamente el segundo recorrido del crédito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->dropColumn('activar_segundo_recorrido');
        });
    }
};
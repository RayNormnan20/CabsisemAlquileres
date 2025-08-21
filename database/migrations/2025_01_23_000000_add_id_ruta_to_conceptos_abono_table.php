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
        Schema::table('conceptos_abono', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ruta')->nullable()->after('id_usuario')
                ->comment('Ruta asociada al concepto de abono');
            
            $table->foreign('id_ruta')
                  ->references('id_ruta')
                  ->on('ruta')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conceptos_abono', function (Blueprint $table) {
            $table->dropForeign(['id_ruta']);
            $table->dropColumn('id_ruta');
        });
    }
};
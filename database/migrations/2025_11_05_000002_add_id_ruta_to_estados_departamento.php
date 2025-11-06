<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estados_departamento', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ruta')->nullable()->after('activo');
            $table->foreign('id_ruta')->references('id_ruta')->on('ruta')->nullOnDelete();
        });

        // Set route 7 for existing estados_departamento if not set
        try {
            DB::table('estados_departamento')->whereNull('id_ruta')->update(['id_ruta' => 7]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        Schema::table('estados_departamento', function (Blueprint $table) {
            $table->dropForeign(['id_ruta']);
            $table->dropColumn('id_ruta');
        });
    }
};
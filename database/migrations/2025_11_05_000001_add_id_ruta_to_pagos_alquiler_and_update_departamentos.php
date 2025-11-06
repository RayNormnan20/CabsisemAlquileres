<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add id_ruta to pagos_alquiler
        Schema::table('pagos_alquiler', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ruta')->nullable()->after('id_alquiler');
            $table->foreign('id_ruta')->references('id_ruta')->on('ruta')->nullOnDelete();
        });

        // Set default route id 7 for existing pagos_alquiler
        try {
            DB::table('pagos_alquiler')->whereNull('id_ruta')->update(['id_ruta' => 7]);
        } catch (\Throwable $e) {
            // Swallow errors to avoid breaking migration if table empty
        }

        // Ensure departamentos have a route; set to 7 where missing
        try {
            DB::table('departamentos')->whereNull('id_ruta')->update(['id_ruta' => 7]);
        } catch (\Throwable $e) {
            // Ignore errors
        }
    }

    public function down(): void
    {
        Schema::table('pagos_alquiler', function (Blueprint $table) {
            $table->dropForeign(['id_ruta']);
            $table->dropColumn('id_ruta');
        });
        // Do not revert departamentos id_ruta values
    }
};
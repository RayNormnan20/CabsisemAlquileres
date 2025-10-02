<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagos_alquiler', function (Blueprint $table) {
            $table->string('foto_1_path')->nullable()->after('recibo_path');
            $table->string('foto_2_path')->nullable()->after('foto_1_path');
            $table->string('foto_3_path')->nullable()->after('foto_2_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagos_alquiler', function (Blueprint $table) {
            $table->dropColumn(['foto_1_path', 'foto_2_path', 'foto_3_path']);
        });
    }
};

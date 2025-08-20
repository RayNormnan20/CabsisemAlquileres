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
        Schema::table('yape_clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_credito')->nullable()->after('id_cliente');
            $table->foreign('id_credito')->references('id_credito')->on('creditos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yape_clientes', function (Blueprint $table) {
            $table->dropForeign(['id_credito']);
            $table->dropColumn('id_credito');
        });
    }
};

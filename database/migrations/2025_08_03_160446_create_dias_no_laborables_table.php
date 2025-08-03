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
        Schema::create('dias_no_laborables', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('motivo')->nullable(); // Ej: "Domingo", "Feriado Nacional", etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dias_no_laborables');
    }
};

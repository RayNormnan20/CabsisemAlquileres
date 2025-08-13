<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estados_departamento', function (Blueprint $table) {
            $table->id('id_estado_departamento');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->string('color', 7)->default('#6B7280'); // Color hex para UI
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estados_departamento');
    }
};

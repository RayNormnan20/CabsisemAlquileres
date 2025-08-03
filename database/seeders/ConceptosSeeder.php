<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptosSeeder extends Seeder
{
    public function run()
    {
        DB::table('conceptos')->insert([
            [
                'nombre' => 'Abono',
                'tipo' => 'Ingresos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Desembolso',
                'tipo' => 'Gastos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Adicional',
                'tipo' => 'Gastos',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
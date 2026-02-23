<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptosSeeder extends Seeder
{
    public function run()
    {
        $conceptos = [
            [
                'nombre' => 'Abono',
                'tipo' => 'Ingresos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Desembolso',
                'tipo' => 'Gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Adicional',
                'tipo' => 'Gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('conceptos')->upsert(
            $conceptos,
            ['nombre'],
            ['tipo', 'updated_at']
        );
    }
}

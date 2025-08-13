<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosDepartamentoSeeder extends Seeder
{
    public function run()
    {
        $estados = [
            [
                'nombre' => 'Disponible',
                'descripcion' => 'Departamento listo para alquilar',
                'color' => '#10B981', // Verde
                'activo' => true
            ],
            [
                'nombre' => 'Ocupado',
                'descripcion' => 'Departamento actualmente alquilado',
                'color' => '#EF4444', // Rojo
                'activo' => true
            ],
            [
                'nombre' => 'Mantenimiento',
                'descripcion' => 'En reparación o mejoras',
                'color' => '#F59E0B', // Amarillo
                'activo' => true
            ],
            [
                'nombre' => 'Reservado',
                'descripcion' => 'Apartado para futuro inquilino',
                'color' => '#3B82F6', // Azul
                'activo' => true
            ],
            [
                'nombre' => 'Desocupado',
                'descripcion' => 'Libre pero necesita limpieza/preparación',
                'color' => '#6B7280', // Gris
                'activo' => true
            ]
        ];

        foreach ($estados as $estado) {
            DB::table('estados_departamento')->insert(array_merge($estado, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
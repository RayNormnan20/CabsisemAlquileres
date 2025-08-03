<?php

namespace Database\Seeders;

use App\Models\DiaNoLaborable;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiasNoLaborablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $anio = now()->year;

        // FERIADOS NACIONALES PERUANOS
        $feriados = [
            "$anio-01-01" => 'Año Nuevo',
            "$anio-05-01" => 'Día del Trabajo',
            "$anio-07-28" => 'Independencia del Perú',
            "$anio-07-29" => 'Fiesta Nacional',
            "$anio-08-30" => 'Santa Rosa de Lima',
            "$anio-10-08" => 'Combate de Angamos',
            "$anio-11-01" => 'Todos los Santos',
            "$anio-12-08" => 'Inmaculada Concepción',
            "$anio-12-25" => 'Navidad',
        ];

        foreach ($feriados as $fecha => $motivo) {
            DiaNoLaborable::firstOrCreate([
                'fecha' => $fecha,
                'motivo' => $motivo,
            ]);
        }

        // TODOS LOS DOMINGOS DEL AÑO ACTUAL
        $fecha = Carbon::createFromDate($anio, 1, 1);
        $finDeAnio = Carbon::createFromDate($anio, 12, 31);

        while ($fecha->lte($finDeAnio)) {
            if ($fecha->isSunday()) {
                DiaNoLaborable::firstOrCreate([
                    'fecha' => $fecha->toDateString(),
                    'motivo' => 'Domingo',
                ]);
            }
            $fecha->addDay();
        }
    }
}

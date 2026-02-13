<?php

namespace Database\Seeders;

use App\Models\DiaNoLaborable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class Domingos2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $anio = 2026;
        $fecha = Carbon::createFromDate($anio, 1, 1);
        $finDeAnio = Carbon::createFromDate($anio, 12, 31);

        $contador = 0;

        $this->command->info("Iniciando la inserción de domingos para el año $anio...");

        while ($fecha->lte($finDeAnio)) {
            if ($fecha->isSunday()) {
                // Usamos firstOrCreate para evitar duplicados si se corre varias veces
                DiaNoLaborable::firstOrCreate(
                    ['fecha' => $fecha->toDateString()],
                    ['motivo' => 'Domingo']
                );
                $contador++;
            }
            $fecha->addDay();
        }

        $this->command->info("Se han insertado/verificado $contador domingos para el año $anio.");
    }
}

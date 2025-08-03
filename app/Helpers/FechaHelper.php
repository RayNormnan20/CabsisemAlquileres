<?php

namespace App\Helpers;

use App\Models\DiaNoLaborable;
use Carbon\Carbon;

class FechaHelper
{
    public static function esDiaNoLaborable(Carbon $fecha): bool
    {
        return DiaNoLaborable::whereDate('fecha', $fecha->format('Y-m-d'))->exists();
    }

    public static function siguienteDiaLaborable(Carbon $fecha): Carbon
    {
        $fecha = $fecha->copy();

        do {
            $fecha->addDay();
        } while (self::esDiaNoLaborable($fecha));

        return $fecha;
    }

    public static function calcularFechasDePago(Carbon $inicio, int $numeroCuotas): array
    {
        $fechas = [];

        $fechaActual = $inicio->copy();
        while (count($fechas) < $numeroCuotas) {
            $fechaActual = self::siguienteDiaLaborable($fechaActual);
            $fechas[] = $fechaActual->copy();
        }

        return $fechas;
    }
}
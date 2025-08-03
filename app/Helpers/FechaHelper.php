<?php

namespace App\Helpers;

use Carbon\Carbon;

class FechaHelper
{
    // Puedes actualizar esta lista o cargarla desde una base de datos
    public static array $feriados = [
        '01-01', // Año Nuevo
        '05-01', // Día del Trabajo
        '07-28', // Independencia del Perú
        '07-29', // Fiesta Nacional
        '08-30', // Santa Rosa de Lima
        '10-08', // Combate de Angamos
        '11-01', // Todos los Santos
        '12-08', // Inmaculada Concepción
        '12-25', // Navidad
    ];

    public static function esDiaNoLaborable(Carbon $fecha): bool
    {
        $esDomingo = $fecha->isSunday();
        $esFeriado = in_array($fecha->format('m-d'), self::$feriados);
        return $esDomingo || $esFeriado;
    }

    public static function siguienteDiaLaborable(Carbon $fecha): Carbon
    {
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
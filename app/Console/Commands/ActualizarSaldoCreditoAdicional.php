<?php

namespace App\Console\Commands;

use App\Models\Creditos;
use App\Models\DiaNoLaborable;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ActualizarSaldoCreditoAdicional extends Command
{

    protected $signature = 'creditos:aplicar-cuota-diaria';
    protected $description = 'Suma la cuota diaria a los créditos adicionales solo en días laborables';

    public function handle()
    {
        $hoy = Carbon::now();

        if ($this->esDiaNoLaborable($hoy)) {
            $this->info("Hoy {$hoy->toDateString()} es día no laborable. No se aplica cuota.");
            return Command::SUCCESS;
        }

        $creditos = Creditos::where('es_adicional', true)
            ->where('saldo_actual', '>', 0)
            ->get();

        $ayerLaborable = $this->obtenerUltimoDiaLaborable($hoy);

        foreach ($creditos as $credito) {
            $cuota = $credito->porcentaje_interes;

            // Si hoy es el primer día laborable después de creado, solo aplicar cuota diaria sin penalidad
            $primerDiaPostCreacion = $this->obtenerPrimerDiaLaborableDespuesDe($credito->created_at);

            if ($hoy->equalTo($primerDiaPostCreacion)) {
                $credito->saldo_actual += $cuota;
                $credito->save();

                $this->info("Primera cuota aplicada a crédito ID {$credito->id_credito} (sin penalidad) - Saldo: S/ {$credito->saldo_actual}");
                continue;
            }

            // Aplicar cuota diaria
            $credito->saldo_actual += $cuota;

            // Verificar si se pagó el día anterior laborable
            if ($credito->created_at->lte($ayerLaborable)) {
                $huboAbono = $credito->abonos()
                    ->whereDate('fecha_pago', $ayerLaborable)
                    ->exists();

                if (!$huboAbono) {
                    // Penalidad: no pagó el día anterior laborable
                    $credito->saldo_actual += $cuota;
                    $this->info("Penalidad aplicada a crédito ID {$credito->id_credito} (sin abono el {$ayerLaborable->toDateString()})");
                }
            }

            $credito->save();
            $this->info("Cuota aplicada a crédito ID {$credito->id_credito} - Nuevo saldo: S/ {$credito->saldo_actual}");
        }

        $this->info("Proceso finalizado.");
        return Command::SUCCESS;
    }


    protected function esDiaNoLaborable(Carbon $fecha): bool
    {
        return $fecha->isSunday() || DiaNoLaborable::whereDate('fecha', $fecha)->exists();
    }

    protected function obtenerUltimoDiaLaborable(Carbon $desde): Carbon
    {
        $dia = $desde->copy()->subDay();

        while ($this->esDiaNoLaborable($dia)) {
            $dia->subDay();
        }

        return $dia;
    }

    protected function obtenerPrimerDiaLaborableDespuesDe(Carbon $fecha): Carbon
    {
        $dia = $fecha->copy()->addDay();

        while ($this->esDiaNoLaborable($dia)) {
            $dia->addDay();
        }

        return $dia;
    }

}
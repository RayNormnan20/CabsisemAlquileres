<?php

namespace App\Console\Commands;

use App\Models\Creditos;
use App\Models\DiaNoLaborable;
use App\Models\LogActividad;
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
            LogActividad::registrar(
                'Créditos Adicionales',
                "Intento de aplicar cuota diaria en día no laborable {$hoy->toDateString()}",
                [
                    'dia_no_laborable' => true,
                    'fecha' => $hoy->toDateString(),
                ]
            );

            $this->info("Hoy {$hoy->toDateString()} es día no laborable. No se aplica cuota.");
            return Command::SUCCESS;
        }

        $creditos = Creditos::where('es_adicional', true)
            ->where('saldo_actual', '>', 0)
            ->get();

        $ayerLaborable = $this->obtenerUltimoDiaLaborable($hoy);

        foreach ($creditos as $credito) {
            $saldoAnterior = $credito->saldo_actual;
            $cuota = $credito->porcentaje_interes;
            $tipoConcepto = $credito->concepto->tipo ?? null;

            // Si hoy es el primer día laborable después de creado, solo aplicar cuota diaria sin penalidad
            $primerDiaPostCreacion = $this->obtenerPrimerDiaLaborableDespuesDe($credito->created_at);

            if ($hoy->equalTo($primerDiaPostCreacion)) {
                $credito->saldo_actual += $cuota;
                $credito->save();

                // Log por crédito: primera cuota sin penalidad
                LogActividad::registrar(
                    'Créditos Adicionales',
                    "Crédito {$credito->id_credito} (" . ($credito->cliente->nombre ?? 'Cliente') . "): cuota S/ {$cuota}, saldo anterior S/ {$saldoAnterior}, nuevo saldo S/ {$credito->saldo_actual} (primera cuota sin penalidad)",
                    [
                        'id_credito' => $credito->id_credito,
                        'primera_cuota' => true,
                        'cuota_diaria_aplicada' => $cuota,
                        'penalidad_aplicada' => false,
                        'penalidad_monto' => 0.0,
                        'saldo_anterior' => $saldoAnterior,
                        'saldo_nuevo' => $credito->saldo_actual,
                        'incremento_total' => $cuota,
                        'cliente_id' => $credito->id_cliente,
                        'cliente_nombre' => $credito->cliente->nombre ?? null,
                        'concepto_id' => $credito->id_concepto,
                        'concepto_nombre' => $credito->concepto->nombre ?? null,
                        'tipo_concepto' => $tipoConcepto,
                        'fecha_proximo_pago' => $credito->fecha_proximo_pago ? $credito->fecha_proximo_pago->toDateString() : null,
                        'fecha_ayer_laborable' => $ayerLaborable->toDateString(),
                    ]
                );
-
-                $this->info("Primera cuota aplicada a crédito ID {$credito->id_credito} (sin penalidad) - Saldo: S/ {$credito->saldo_actual}");
+                $this->info("Crédito {$credito->id_credito} (" . ($credito->cliente->nombre ?? 'Cliente') . "): cuota S/ {$cuota}, saldo anterior S/ {$saldoAnterior}, nuevo saldo S/ {$credito->saldo_actual} (sin penalidad)");
                 continue;
            }

            // Aplicar cuota diaria
            $credito->saldo_actual += $cuota;
            
            // Inicializar variables de detalle
            $penalidadAplicada = false;
            $penalidadMonto = 0.0;
            $huboAbono = null;

            // Verificar si se pagó el día anterior laborable
            if ($credito->created_at->lte($ayerLaborable)) {
                $huboAbono = $credito->abonos()
                    ->whereDate('fecha_pago', $ayerLaborable)
                    ->exists();

                 if (!$huboAbono) {
                     // Penalidad: no pagó el día anterior laborable
                     $credito->saldo_actual += $cuota;
                     $penalidadAplicada = true;
                     $penalidadMonto = $cuota;
                     $this->info("Penalidad aplicada a crédito ID {$credito->id_credito} (sin abono el {$ayerLaborable->toDateString()})");
                     LogActividad::registrar(
                         'Créditos Adicionales',
                         "Penalidad aplicada a crédito ID {$credito->id_credito} (sin abono el {$ayerLaborable->toDateString()})",
                         [
                             'id_credito' => $credito->id_credito,
                             'penalidad_aplicada' => true,
                             'penalidad_monto' => $penalidadMonto,
                             'fecha_ayer_laborable' => $ayerLaborable->toDateString(),
                             'cliente_id' => $credito->id_cliente,
                             'cliente_nombre' => $credito->cliente->nombre ?? null,
                         ]
                     );
                 }
            }

            // Calcular incremento total respecto al saldo anterior
            $incrementoTotal = $credito->saldo_actual - $saldoAnterior;

            $credito->save();
            
            // Mensaje detallado en consola con cuenta, monto sumado y nuevo valor
            $this->info("Crédito {$credito->id_credito} (" . ($credito->cliente->nombre ?? 'Cliente') . ") (tipo: " . ($tipoConcepto ?? 'N/A') . "): cuota S/ {$cuota}" . ($penalidadAplicada ? ", penalidad S/ {$penalidadMonto}" : "") . ", saldo anterior S/ {$saldoAnterior}, nuevo saldo S/ {$credito->saldo_actual}");
            
            // Log por crédito: detalle completo
            LogActividad::registrar(
                'Créditos Adicionales',
                "Crédito {$credito->id_credito} (" . ($credito->cliente->nombre ?? 'Cliente') . "): cuota S/ {$cuota}" . ($penalidadAplicada ? ", penalidad S/ {$penalidadMonto}" : "") . ", saldo anterior S/ {$saldoAnterior}, nuevo saldo S/ {$credito->saldo_actual}",
                [
                    'id_credito' => $credito->id_credito,
                    'primera_cuota' => false,
                    'cuota_diaria_aplicada' => $cuota,
                    'penalidad_aplicada' => $penalidadAplicada,
                    'penalidad_monto' => $penalidadMonto,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $credito->saldo_actual,
                    'abono_ayer' => $huboAbono,
                    'incremento_total' => $incrementoTotal,
                    'cliente_id' => $credito->id_cliente,
                    'cliente_nombre' => $credito->cliente->nombre ?? null,
                    'concepto_id' => $credito->id_concepto,
                    'concepto_nombre' => $credito->concepto->nombre ?? null,
                    'tipo_concepto' => $tipoConcepto,
                    'fecha_proximo_pago' => $credito->fecha_proximo_pago ? $credito->fecha_proximo_pago->toDateString() : null,
                    'fecha_ayer_laborable' => $ayerLaborable->toDateString(),
                ]
            );

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
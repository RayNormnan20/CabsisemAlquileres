<?php

namespace App\Filament\Widgets;

use App\Models\Abonos;
use Filament\Widgets\Widget;
use App\Models\Clientes;
use App\Models\Creditos;
use Illuminate\Support\Facades\Session;

class FinancialStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-stats-widget';

    protected int | string | array $columnSpan = 'full';


    protected $listeners = ['globalRouteChanged' => 'refreshWidgetData'];

    public function refreshWidgetData(): void
    {
        // No necesitas añadir lógica aquí. La simple llamada a este método
        // por el listener es suficiente para que Livewire sepa que debe
        // re-renderizar el widget y, por lo tanto, volver a ejecutar getViewData().
    }


    protected function getViewData(): array
    {
        // Obtiene el ID de la ruta seleccionada de la sesión
        $selectedRutaId = Session::get('selected_ruta_id');

        // Inicializa las variables para los conteos y sumas
        $totalClientesRuta = 0;
        $totalCreditosRutaCount = 0;
        $totalCreditosRutaSum = 0;
        $totalAbonosRutaCount = 0;
        $totalAbonosRutaSum = 0;
        $cuotasPendientes = 0;
        $cuotasVencidas = 0;
        $cuotasHoy = 0;
        $cuotasPagadasHoy = 0;
        $totalAbonosHoyCount = 0;
        $totalCuotasHoySum = 0; // Nueva variable para sumar el valor de cuotas de hoy
        $totalPagosYapeHoy = 0; // Total de pagos Yape del día
        $totalPagosEfectivoHoy = 0; // Total de pagos Efectivo del día
        $countPagosYapeHoy = 0; // Cantidad de pagos Yape del día
        $countPagosEfectivoHoy = 0; // Cantidad de pagos Efectivo del día

        // --- Lógica para Clientes por Ruta ---
        $clientesQuery = Clientes::query();
        if ($selectedRutaId) {
            $clientesQuery->where('id_ruta', $selectedRutaId);
        }
        $totalClientesRuta = $clientesQuery->count();

        // --- Lógica para Créditos por Ruta ---
        $creditosQuery = Creditos::query();
        if ($selectedRutaId) {
            // Filtra créditos cuyos clientes pertenecen a la ruta seleccionada
            $creditosQuery->whereHas('cliente', function ($query) use ($selectedRutaId) {
                $query->where('id_ruta', $selectedRutaId);
            });
        }
        $totalCreditosRutaCount = $creditosQuery->count();
        // Asume que la tabla 'creditos' tiene una columna 'monto' para la suma
        $totalCreditosRutaSum = $creditosQuery->sum('valor_credito'); 

        // --- Lógica para Abonos por Ruta ---
        $abonosQuery = Abonos::query();
        if ($selectedRutaId) {
            // Filtra abonos que están relacionados a créditos, cuyos clientes pertenecen a la ruta
            $abonosQuery->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                $query->where('id_ruta', $selectedRutaId);
            });
        }
        $totalAbonosRutaCount = $abonosQuery->count();
        $totalAbonosRutaSum = $abonosQuery->sum('monto_abono');

        $abonosHoyQuery = Abonos::query()->whereDate('created_at', now()->toDateString());
        if ($selectedRutaId) {
            $abonosHoyQuery->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                $query->where('id_ruta', $selectedRutaId);
            });
        }
        $totalAbonosHoyCount = $abonosHoyQuery->count();
        $totalAbonosHoySum = $abonosHoyQuery->sum('monto_abono');

        // --- Lógica para Pagos Yape del día ---
        $pagosYapeHoyQuery = Abonos::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereHas('conceptosabonos', function ($query) {
                $query->where('tipo_concepto', 'Yape');
            });
        if ($selectedRutaId) {
            $pagosYapeHoyQuery->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                $query->where('id_ruta', $selectedRutaId);
            });
        }
        $countPagosYapeHoy = $pagosYapeHoyQuery->count();
        $totalPagosYapeHoy = $pagosYapeHoyQuery->sum('monto_abono');

        // --- Lógica para Pagos Efectivo del día ---
        $pagosEfectivoHoyQuery = Abonos::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereHas('conceptosabonos', function ($query) {
                $query->where('tipo_concepto', 'Efectivo');
            });
        if ($selectedRutaId) {
            $pagosEfectivoHoyQuery->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                $query->where('id_ruta', $selectedRutaId);
            });
        }
        $countPagosEfectivoHoy = $pagosEfectivoHoyQuery->count();
        $totalPagosEfectivoHoy = $pagosEfectivoHoyQuery->sum('monto_abono');

        // Cálculo de cuotas
        $creditos = $creditosQuery->get();
        $hoy = now()->toDateString();

        foreach ($creditos as $credito) {
            // Cuotas pendientes totales
            $cuotasPendientes += $credito->cuotas_pendientes;
            
            // Cuotas vencidas
            if ($credito->fecha_proximo_pago && $credito->fecha_proximo_pago < now()) {
                $cuotasVencidas += 1;
            }
            
            // Cuotas programadas para hoy 
            if ($credito->fecha_proximo_pago && $credito->fecha_proximo_pago->toDateString() == $hoy) {
                $cuotasHoy += 1;
                $totalCuotasHoySum += $credito->valor_cuota; // Suma el valor de la cuota
                
                // Verificar si ya pagó la cuota de hoy
                $pagoHoy = $credito->abonos->contains(function($abono) use ($hoy) {
                    return $abono->created_at->toDateString() == $hoy;
                });
                
                if ($pagoHoy) {
                    $cuotasPagadasHoy += 1;
                }
            }
        }

        return [
            'totalClientesRuta' => $totalClientesRuta,
            'totalCreditosRutaCount' => $totalCreditosRutaCount,
            'totalCreditosRutaSum' => number_format($totalCreditosRutaSum, 2, ',', '.'),
            'totalAbonosRutaCount' => $totalAbonosRutaCount,
            'totalAbonosRutaSum' => number_format($totalAbonosRutaSum, 2, ',', '.'),
            'cuotasPendientes' => $cuotasPendientes,
            'cuotasVencidas' => $cuotasVencidas,
            'cuotasHoy' => $cuotasHoy,
            'cuotasPagadasHoy' => $cuotasPagadasHoy,
            'totalAbonosHoyCount' => $totalAbonosHoyCount,
            'totalAbonosHoySum' => number_format($totalAbonosHoySum, 2, ',', '.'),
            'totalCuotasHoySum' => number_format($totalCuotasHoySum, 2, ',', '.'),
            'totalPagosYapeHoy' => number_format($totalPagosYapeHoy, 2, ',', '.'),
            'totalPagosEfectivoHoy' => number_format($totalPagosEfectivoHoy, 2, ',', '.'),
            'countPagosYapeHoy' => $countPagosYapeHoy,
            'countPagosEfectivoHoy' => $countPagosEfectivoHoy,
        ];
    }
}
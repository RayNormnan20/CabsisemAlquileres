<?php

namespace App\Filament\Widgets;

use App\Models\Alquiler;
use App\Models\ClienteAlquiler;
use App\Models\PagoAlquiler;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class AlquilerStatsOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-stats-widget';
    
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    // Optional: Refresh every 30 seconds
    protected static ?string $pollingInterval = '30s';

    protected function getViewData(): array
    {
        $today = Carbon::today();
        $rutaId = Session::get('selected_ruta_id');

        // Helper queries with optional route filtering
        
        // Alquileres query
        $alquileresQuery = Alquiler::query()
            ->when($rutaId, function ($query) use ($rutaId) {
                $query->whereHas('departamento', function ($q) use ($rutaId) {
                    $q->where('id_ruta', $rutaId);
                });
            });

        // Pagos query
        $pagosQuery = PagoAlquiler::query()
            ->when($rutaId, function ($query) use ($rutaId) {
                $query->where('id_ruta', $rutaId);
            });
            
        // Clientes query
        $clientesQuery = ClienteAlquiler::query()
            ->when($rutaId, function ($query) use ($rutaId) {
                $query->where('id_ruta', $rutaId);
            });

        // 1. POR COBRAR
        // Sum of monthly price for active rentals where payment date is today or in the past
        $porCobrar = (clone $alquileresQuery)->where('estado_alquiler', 'activo')
            ->where('fecha_proximo_pago', '<=', $today)
            ->sum('precio_mensual');
        
        // 2. PAGOS HOY
        $pagosHoy = (clone $pagosQuery)->whereDate('fecha_pago', $today)->sum('monto_pagado');

        // 3. Cuotas Hoy (Due Today)
        $cuotasHoy = (clone $alquileresQuery)->where('estado_alquiler', 'activo')
            ->whereDate('fecha_proximo_pago', $today)
            ->count();

        // 4. Pagadas Hoy (Payments Count Today)
        $pagadasHoy = (clone $pagosQuery)->whereDate('fecha_pago', $today)->count();

        // 5. CLIENTES
        $clientesCount = (clone $clientesQuery)->count();

        // 6. ALQUILERES (Active)
        $alquileresActivosCount = (clone $alquileresQuery)->where('estado_alquiler', 'activo')->count();
        $alquileresActivosMonto = (clone $alquileresQuery)->where('estado_alquiler', 'activo')->sum('precio_mensual');

        // 7. Breakdown of payments
        $pagosDigitalQuery = (clone $pagosQuery)->whereDate('fecha_pago', $today)
            ->where(function($query) {
                $query->where('metodo_pago', 'like', '%transferencia%')
                      ->orWhere('metodo_pago', 'like', '%yape%')
                      ->orWhere('metodo_pago', 'like', '%plin%');
            });
            
        $totalPagosDigitalHoy = (clone $pagosDigitalQuery)->sum('monto_pagado');
        $countPagosDigitalHoy = (clone $pagosDigitalQuery)->count();
            
        $pagosEfectivoQuery = (clone $pagosQuery)->whereDate('fecha_pago', $today)
            ->where('metodo_pago', 'efectivo');
            
        $totalPagosEfectivoHoy = (clone $pagosEfectivoQuery)->sum('monto_pagado');
        $countPagosEfectivoHoy = (clone $pagosEfectivoQuery)->count();

        return [
            'porCobrar' => $porCobrar,
            'pagosHoy' => $pagosHoy,
            'cuotasHoy' => $cuotasHoy,
            'pagadasHoy' => $pagadasHoy,
            'clientesCount' => $clientesCount,
            'alquileresActivosCount' => $alquileresActivosCount,
            'alquileresActivosMonto' => $alquileresActivosMonto,
            'totalPagosDigitalHoy' => $totalPagosDigitalHoy,
            'countPagosDigitalHoy' => $countPagosDigitalHoy,
            'totalPagosEfectivoHoy' => $totalPagosEfectivoHoy,
            'countPagosEfectivoHoy' => $countPagosEfectivoHoy,
        ];
    }
}

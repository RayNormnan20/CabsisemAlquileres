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
        // Asume que la tabla 'abonos' tiene una columna 'monto_abonado' para la suma
        $totalAbonosRutaSum = $abonosQuery->sum('monto_abono'); 

        return [
            'cuaActual' => '-439,856', // Se mantienen estos valores fijos
            'cuaAnterior' => '-442,121', // Se mantienen estos valores fijos
            'ingresosGastos' => [6000, 3000, 2000, 3000, 2000], // Se mantienen estos valores fijos
            'totalClientesRuta' => $totalClientesRuta,
            'totalCreditosRutaCount' => $totalCreditosRutaCount,
            'totalCreditosRutaSum' => number_format($totalCreditosRutaSum, 2, ',', '.'), // Formato de moneda
            'totalAbonosRutaCount' => $totalAbonosRutaCount,
            'totalAbonosRutaSum' => number_format($totalAbonosRutaSum, 2, ',', '.'),     // Formato de moneda
        ];
    }
}
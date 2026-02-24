<?php

namespace App\Filament\Widgets;

use App\Models\PagoAlquiler;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IngresosAlquilerChart extends LineChartWidget
{
    protected static ?string $heading = 'INGRESOS ALQUILER últimos 10 días';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $rutaId = Session::get('selected_ruta_id');
        $startDate = Carbon::now()->subDays(9)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $query = PagoAlquiler::query()
            ->whereBetween('fecha_pago', [$startDate, $endDate]);

        if ($rutaId) {
            $query->where('id_ruta', $rutaId);
        }
            
        $pagos = $query->get();
        
        $labels = [];
        $ingresosData = [];
        
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d M'); 
            
            $daySum = $pagos->filter(function ($item) use ($dateString) {
                return Carbon::parse($item->fecha_pago)->format('Y-m-d') === $dateString;
            })->sum('monto_pagado');
            
            $ingresosData[] = $daySum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos Alquiler',
                    'data' => $ingresosData,
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => '#3b82f6',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

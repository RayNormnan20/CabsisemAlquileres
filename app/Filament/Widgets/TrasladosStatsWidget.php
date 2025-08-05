<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TrasladosStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Traslados Este Mes', '0')
                ->description('Traslados realizados en el mes actual')
                ->descriptionIcon('heroicon-s-calendar')
                ->color('success'),
                
            Card::make('Total Traslados', '0')
                ->description('Traslados realizados históricamente')
                ->descriptionIcon('heroicon-s-chart-bar')
                ->color('primary'),
                
            Card::make('Ruta con Más Clientes', 'Sin datos')
                ->description('Ruta que tiene más clientes activos')
                ->descriptionIcon('heroicon-s-users')
                ->color('warning'),
        ];
    }
}
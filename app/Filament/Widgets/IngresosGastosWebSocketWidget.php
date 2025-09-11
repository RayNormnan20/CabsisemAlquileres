<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class IngresosGastosWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.ingresos-gastos-websocket';
    
    protected int|string|array $columnSpan = 'full';
    
    protected bool $isLazy = false;
    
    protected function getViewData(): array
    {
        return [
            'movimientoId' => null
        ];
    }
}
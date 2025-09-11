<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PagosAlquilerWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.pagos-alquiler-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'pagoAlquilerId' => null, // Puede ser usado para contexto específico si es necesario
        ];
    }
}
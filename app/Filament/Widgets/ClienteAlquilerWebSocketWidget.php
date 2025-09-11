<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ClienteAlquilerWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.cliente-alquiler-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'clienteAlquilerId' => null, // Puede ser usado para contexto específico si es necesario
        ];
    }
}
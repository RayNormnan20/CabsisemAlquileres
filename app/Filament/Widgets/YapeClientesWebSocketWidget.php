<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class YapeClientesWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.yape-clientes-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'yapeClienteId' => null, // Puede ser usado para contexto específico si es necesario
        ];
    }
}
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class EdificiosWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.edificios-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'edificioId' => null, // Puede ser usado para contexto específico si es necesario
        ];
    }
}
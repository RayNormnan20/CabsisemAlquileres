<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DepartamentosWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.departamentos-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'departamentoId' => null, // Puede ser usado para contexto específico si es necesario
        ];
    }
}
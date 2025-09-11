<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PlanillaRecaudadorWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.planilla-recaudador-websocket';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getViewData(): array
    {
        return [
            'planillaRecaudadorId' => null
        ];
    }
}
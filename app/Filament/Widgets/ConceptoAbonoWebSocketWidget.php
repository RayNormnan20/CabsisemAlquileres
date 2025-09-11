<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ConceptoAbonoWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.concepto-abono-websocket';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        return [
            'conceptoAbonoId' => null,
        ];
    }
}
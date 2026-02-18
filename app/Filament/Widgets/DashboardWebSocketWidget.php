<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardWebSocketWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-websocket';
    protected int|string|array $columnSpan = 'full';
    protected bool $isLazy = false;

    protected function getViewData(): array
    {
        return [
            'rutaId' => session('selected_ruta_id'),
        ];
    }
}

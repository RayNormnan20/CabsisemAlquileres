<?php

namespace App\Filament\Pages;

use App\Models\Ruta;
use App\Filament\Widgets\DashboardWebSocketWidget;
use App\Filament\Widgets\PagosAlquilerDashboardWidget;
use Filament\Pages\Dashboard as BasePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Session;

class Dashboard extends BasePage
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getHeading(): string|Htmlable
    {
        // Ocultar el encabezado de la página ("Escritorio")
        return '';
    }

    public function mount(): void
    {
        parent::mount();
        
        // Verificar si hay una ruta seleccionada en la sesión
        if (!Session::has('selected_ruta_id')) {
            $user = auth()->user();
            $ruta = null;

            // 1) Priorizar la última ruta persistida en el usuario
            if (!empty($user->last_selected_ruta_id)) {
                $ruta = Ruta::find($user->last_selected_ruta_id);
            }

            // 2) Si no existe, tomar la primera ruta activa asignada al usuario
            if (!$ruta) {
                $ruta = $user->rutas()->where('activa', true)->first();
            }

            // 3) Si el usuario no tiene rutas activas, tomar la primera activa global
            if (!$ruta) {
                $ruta = Ruta::where('activa', true)->first();
            }

            if ($ruta) {
                Session::put('selected_ruta_id', $ruta->id_ruta);
                Session::put('selected_ruta_name', $ruta->nombre_completo ?? $ruta->nombre);
                
                // Disparar notificación para el usuario
                $this->notify('success', 'Ruta seleccionada: ' . ($ruta->nombre_completo ?? $ruta->nombre));
                
                // Forzar recarga de los widgets
                $this->redirect(request()->url());
            }
        }
    }

    protected function getColumns(): int | array
    {
        return 1;

    }

    protected function getWidgets(): array
    {
        return [
            DashboardWebSocketWidget::class,
            PagosAlquilerDashboardWidget::class,
        ];
    }
}

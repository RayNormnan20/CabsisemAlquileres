<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientesPorRenovarWidget;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\SegundoRecorridoWidget;
use App\Filament\Widgets\YapeClientesTableWidget;
use App\Filament\Widgets\YapesTotalesDelDiaWidget;
use App\Filament\Widgets\YapeUsuariosWidget;
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
            // Si no hay, cargar la primera ruta activa del usuario
            $user = auth()->user();
            $ruta = $user->rutas()->where('activa', true)->first();
            
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
                   FinancialStatsWidget::class,
                   YapeClientesTableWidget::class,
                   ClientesPorRenovarWidget::class,
                   YapesTotalesDelDiaWidget::class,
                   SegundoRecorridoWidget::class,
                   YapeUsuariosWidget::class,
                   

        ];
    }
}
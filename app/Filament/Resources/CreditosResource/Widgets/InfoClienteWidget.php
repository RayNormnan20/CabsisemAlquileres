<?php

namespace App\Filament\Resources\CreditosResource\Widgets;

use App\Models\Creditos;
use Filament\Widgets\Widget;
// No necesitas 'use Filament\Forms\Components\View;' aquí, ya que no estás construyendo un formulario.

class InfoClienteWidget extends Widget
{
    protected static string $view = 'filament.resources.creditos-resource.widgets.info-cliente-widget';

    // <--- ESTA LÍNEA ES CLAVE: Asegura que el widget ocupe todo el ancho
    protected int|string|array $columnSpan = 'full';

    public Creditos $record;

    // <--- ELIMINA ESTE MÉTODO POR COMPLETO
    // protected function getFormSchema(): array
    // {
    //     return [
    //         View::make('filament.resources.creditos-resource.widgets.info-cliente-widget'),
    //     ];
    // }
}
<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\Concerns\HasRecord;

class ViewCredito extends ViewRecord
{
    protected static string $resource = CreditosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // \Filament\Actions\EditAction::make(),
        ];
    }

    protected function getTitle(): string
    {
        return 'Historial de Abonos'; // Deja el título vacío
    }

    public function getFormActions(): array
    {
        return []; // Evita mostrar acciones como Editar, Guardar, etc.
    }

    protected function getFormSchema(): array
    {
        return []; // Evita completamente el formulario
    }

   protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\CreditosResource\Widgets\InfoClienteWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\CreditosResource\Widgets\HistorialAbonosWidget::class,

        ];
    }


    protected function hasFormActions(): bool
    {
        return false; // Quita botones debajo del form (si quedaran)
    }
}
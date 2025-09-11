<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Models\ConceptoAbono;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\ConceptoAbonoWebSocketWidget;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListConceptosAbonos extends ListRecords
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function getTableQuery(): Builder
    {
        $query = ConceptoAbono::query()
            ->whereNull('id_abono');
            
        $rutaSeleccionada = Session::get('selected_ruta_id');
        if ($rutaSeleccionada) {
            $query->where('id_ruta', $rutaSeleccionada);
        }
        
        return $query;
    }
       protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // Esto oculta el botón "Crear Concepto"
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ConceptoAbonoWebSocketWidget::class,
        ];
    }
}
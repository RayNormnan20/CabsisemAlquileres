<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Models\ConceptoAbono;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Builder;

class ListConceptosAbonos extends ListRecords
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function getTableQuery(): Builder
    {
        return ConceptoAbono::query()
            ->whereNull('id_abono');
    }
       protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // Esto oculta el botón "Crear Concepto"
        ];
    }
}
<?php

namespace App\Filament\Resources\YapeClienteResource\Pages;

use App\Filament\Resources\YapeClienteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListYapeClientes extends ListRecords
{
    protected static string $resource = YapeClienteResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Filtrar por ruta desde la sesión
        $rutaId = Session::get('selected_ruta_id');
        if ($rutaId) {
            $query->whereHas('cliente', function($q) use ($rutaId) {
                $q->where('id_ruta', $rutaId);
            });
        }

        return $query;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(!Session::get('selected_ruta_id'))
                ->tooltip(fn () => !Session::get('selected_ruta_id')
                    ? 'Seleccione una ruta primero'
                    : 'Crear nuevo Yape Cliente'),
        ];
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
}
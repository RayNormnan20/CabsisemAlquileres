<?php

namespace App\Filament\Resources\RutasResource\Pages;

use App\Filament\Resources\RutasResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRutas extends ListRecords
{
    protected static string $resource = RutasResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Ruta')
                ->icon('heroicon-s-plus')
                ->url('/rutas/create'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['oficina', 'usuarios']);
    }
}
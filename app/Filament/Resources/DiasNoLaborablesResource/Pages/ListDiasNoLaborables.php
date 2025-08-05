<?php

namespace App\Filament\Resources\DiasNoLaborablesResource\Pages;

use App\Filament\Resources\DiasNoLaborablesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiasNoLaborables extends ListRecords
{
    protected static string $resource = DiasNoLaborablesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Día No Laborable')
                ->icon('heroicon-s-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí puedes agregar widgets si los necesitas
        ];
    }
}

<?php

namespace App\Filament\Resources\LogActividadResource\Pages;

use App\Filament\Resources\LogActividadResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogActividad extends EditRecord
{
    protected static string $resource = LogActividadResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Comentado para ocultar la opción de eliminar registros del log
        ];
    }
}

<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Session;

class EditConceptosAbonos extends EditRecord
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asignar la ruta de la sesión si no tiene ruta asignada
        if (!isset($data['id_ruta']) || $data['id_ruta'] === null) {
            $rutaSesion = Session::get('selected_ruta_id');
            if ($rutaSesion) {
                $data['id_ruta'] = $rutaSesion;
            }
        }

        // Asignar id_usuario automáticamente si no está presente
        if (!isset($data['id_usuario']) || $data['id_usuario'] === null) {
            $data['id_usuario'] = auth()->id();
        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
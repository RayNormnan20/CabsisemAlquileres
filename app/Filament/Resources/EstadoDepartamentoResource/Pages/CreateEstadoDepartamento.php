<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use App\Models\LogActividad;
use Illuminate\Support\Facades\Session;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoDepartamento extends CreateRecord
{
    protected static string $resource = EstadoDepartamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar id_ruta desde la ruta seleccionada en sesión
        $rutaId = Session::get('selected_ruta_id');
        if (!$rutaId) {
            throw new \Exception('Debe seleccionar una Ruta antes de crear un estado de departamento.');
        }
        $data['id_ruta'] = $rutaId;
        return $data;
    }
    protected function afterCreate(): void
    {
        // Registrar log de actividad
        LogActividad::registrar(
            'Estados de Departamento',
            'Creó un nuevo estado de departamento: ' . $this->record->nombre,
            [
                'id_estado_departamento' => $this->record->id_estado_departamento,
                'nombre' => $this->record->nombre,
                'descripcion' => $this->record->descripcion,
                'color' => $this->record->color,
                'activo' => $this->record->activo,
                'id_ruta' => $this->record->id_ruta
            ]
        );
    }

     protected function getCreatedNotificationTitle(): ?string
    {
        return 'Estado de departamento creado exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
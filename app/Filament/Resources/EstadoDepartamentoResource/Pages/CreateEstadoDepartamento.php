<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoDepartamento extends CreateRecord
{
    protected static string $resource = EstadoDepartamentoResource::class;
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
                'activo' => $this->record->activo
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
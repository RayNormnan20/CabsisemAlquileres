<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstadoDepartamento extends EditRecord
{
    protected static string $resource = EstadoDepartamentoResource::class;

    protected function afterSave(): void
    {
        // Registrar log de actividad para actualización
        LogActividad::registrar(
            'Estados de Departamento',
            'Editó el estado de departamento: ' . $this->record->nombre,
            [
                'id_estado_departamento' => $this->record->id_estado_departamento,
                'nombre' => $this->record->nombre,
                'descripcion' => $this->record->descripcion,
                'color' => $this->record->color,
                'activo' => $this->record->activo,
                'cambios' => $this->record->getChanges()
            ]
        );
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Registrar log de actividad para eliminación
                    LogActividad::registrar(
                        'Estados de Departamento',
                        'Eliminó el estado de departamento: ' . $this->record->nombre,
                        [
                            'id_estado_departamento' => $this->record->id_estado_departamento,
                            'nombre' => $this->record->nombre,
                            'descripcion' => $this->record->descripcion
                        ]
                    );
                }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}

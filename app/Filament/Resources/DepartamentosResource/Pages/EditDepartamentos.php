<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartamentos extends EditRecord
{
    protected static string $resource = DepartamentosResource::class;

    protected function afterSave(): void
    {
        // Registrar log de actividad para actualización
        LogActividad::create([
            'user_id' => auth()->id(),
            'tipo' => 'Departamentos',
            'mensaje' => 'Departamento actualizado: ' . $this->record->numero_departamento,
            'metadata' => [
                'accion' => 'actualizar',
                'id_departamento' => $this->record->id_departamento,
                'numero_departamento' => $this->record->numero_departamento,
                'piso' => $this->record->piso,
                'id_edificio' => $this->record->id_edificio,
                'id_estado_departamento' => $this->record->id_estado_departamento,
                'precio_alquiler' => $this->record->precio_alquiler,
                'id_ruta' => $this->record->id_ruta,
                'activo' => $this->record->activo,
                'cambios' => $this->record->getChanges(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ]);
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($action) {
                    // Validar que el departamento no tenga alquileres activos
                    if ($this->record->tieneAlquilerActivo()) {
                        \Filament\Notifications\Notification::make()
                            ->title('No se puede eliminar el departamento')
                            ->body("El departamento '{$this->record->numero_departamento}' tiene alquileres activos. Debe finalizar primero todos los alquileres antes de eliminar el departamento.")
                            ->danger()
                            ->duration(5000)
                            ->send();

                        // Cancelar la acción y cerrar el modal
                        $action->cancel();
                    }
                })
                ->after(function () {
                    // Registrar log de actividad para eliminación
                    LogActividad::create([
                        'user_id' => auth()->id(),
                        'tipo' => 'Departamentos',
                        'mensaje' => 'Departamento eliminado: ' . $this->record->numero_departamento,
                        'metadata' => [
                            'accion' => 'eliminar',
                            'id_departamento' => $this->record->id_departamento,
                            'numero_departamento' => $this->record->numero_departamento,
                            'piso' => $this->record->piso,
                            'id_edificio' => $this->record->id_edificio,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent()
                        ]
                    ]);
                }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}

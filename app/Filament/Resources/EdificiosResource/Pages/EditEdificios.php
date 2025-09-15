<?php

namespace App\Filament\Resources\EdificiosResource\Pages;

use App\Filament\Resources\EdificiosResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEdificios extends EditRecord
{
    protected static string $resource = EdificiosResource::class;

    protected function afterSave(): void
    {
        // Registrar log de actividad para actualización
        LogActividad::create([
            'user_id' => auth()->id(),
            'tipo' => 'Edificios',
            'mensaje' => 'Edificio actualizado: ' . $this->record->nombre,
            'metadata' => [
                'accion' => 'actualizar',
                'id_edificio' => $this->record->id_edificio,
                'nombre' => $this->record->nombre,
                'direccion' => $this->record->direccion,
                'ciudad' => $this->record->ciudad,
                'numero_pisos' => $this->record->numero_pisos,
                'id_cliente_alquiler' => $this->record->id_cliente_alquiler,
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
                    // Validar que el edificio no tenga departamentos asociados
                    $cantidadDepartamentos = $this->record->departamentos()->count();

                    if ($cantidadDepartamentos > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('No se puede eliminar el edificio')
                            ->body("Este edificio tiene {$cantidadDepartamentos} departamento(s) asociado(s). Debe eliminar primero todos los departamentos antes de eliminar el edificio.")
                            ->danger()
                            ->send();

                        // Cancelar la acción y cerrar el modal
                        $action->cancel();
                    }
                })
                ->after(function () {
                    // Registrar log de actividad para eliminación
                    LogActividad::create([
                        'user_id' => auth()->id(),
                        'tipo' => 'Edificios',
                        'mensaje' => 'Edificio eliminado: ' . $this->record->nombre,
                        'metadata' => [
                            'accion' => 'eliminar',
                            'id_edificio' => $this->record->id_edificio,
                            'nombre' => $this->record->nombre,
                            'direccion' => $this->record->direccion,
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
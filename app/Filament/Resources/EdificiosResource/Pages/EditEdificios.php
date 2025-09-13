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
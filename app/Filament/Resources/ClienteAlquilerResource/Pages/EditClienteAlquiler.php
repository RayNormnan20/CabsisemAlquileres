<?php

namespace App\Filament\Resources\ClienteAlquilerResource\Pages;

use App\Filament\Resources\ClienteAlquilerResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditClienteAlquiler extends EditRecord
{
    protected static string $resource = ClienteAlquilerResource::class;

    protected function afterSave(): void
    {
        // Registrar la edición en el log
        LogActividad::registrar(
            'Cliente Alquiler',
            'Editó el cliente/inquilino: ' . $this->record->nombre_completo,
            [
                'cliente_id' => $this->record->id_cliente_alquiler,
                'nombre_completo' => $this->record->nombre_completo,
                'cedula' => $this->record->cedula,
                'telefono' => $this->record->telefono,
                'email' => $this->record->email,
                'direccion' => $this->record->direccion,
                'estado_cliente' => $this->record->estado_cliente
            ]
        );

        Notification::make()
            ->title('Cliente de alquiler actualizado exitosamente')
            ->success()
            ->duration(5000)
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($action) {
                    // Verificar si el cliente tiene alquileres activos
                    if ($this->record->tieneAlquilerActivo()) {
                        Notification::make()
                            ->title('No se puede eliminar')
                            ->body('Este cliente tiene alquileres activos y no puede ser eliminado.')
                            ->danger()
                            ->duration(5000)
                            ->send();

                        // Cancelar la acción y cerrar el modal
                        $action->cancel();
                    }
                })
                ->after(function () {
                    // Registrar la eliminación en el log
                    LogActividad::registrar(
                        'Cliente Alquiler',
                        'Eliminó el cliente/inquilino: ' . $this->record->nombre_completo,
                        [
                            'cliente_id' => $this->record->id_cliente_alquiler,
                            'nombre_completo' => $this->record->nombre_completo,
                            'cedula' => $this->record->cedula,
                            'telefono' => $this->record->telefono,
                            'email' => $this->record->email,
                            'direccion' => $this->record->direccion,
                            'estado_cliente' => $this->record->estado_cliente
                        ]
                    );
                    
                    Notification::make()
                        ->title('Cliente de alquiler eliminado exitosamente')
                        ->success()
                        ->duration(5000)
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
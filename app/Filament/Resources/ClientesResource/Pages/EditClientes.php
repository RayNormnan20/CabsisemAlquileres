<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use App\Models\LogActividad; // ¡Importa el modelo LogActividad!
use App\Events\ClienteUpdated; // Importar el evento ClienteUpdated
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // Opcional: para mostrar notificaciones

class EditClientes extends EditRecord
{
    protected static string $resource = ClientesResource::class;

    // Método que se ejecuta DESPUÉS de guardar un registro (editar)
    protected function afterSave(): void
    {
        // Registra la actividad de edición
        LogActividad::registrar(
            'Clientes', // Tipo de actividad
            'Actualizó los datos del cliente: ' . $this->record->nombre . ' ' . $this->record->apellido, // Mensaje
            [ // Metadata adicional
                'cliente_id' => $this->record->id_cliente,
                'documento' => $this->record->numero_documento,
                'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                'cambios_realizados' => $this->record->getChanges(), // Esto te dará los cambios que se hicieron
                //'negocio' => $this->record->nombre_negocio ?? 'Sin negocio'
            ]
        );

        // Disparar evento ClienteUpdated manualmente para asegurar broadcasting
        event(new ClienteUpdated($this->record));
        
        // Opcional: Notificación de éxito
        Notification::make()
            ->title('Cliente actualizado exitosamente')
            ->success()
            ->send();
    }

    protected function getActions(): array
    {
        return [
            // Re-agrega el DeleteAction aquí para manejar la eliminación desde la página de edición
            Actions\DeleteAction::make()
                ->before(function () {
                    // Registra la actividad ANTES de que el registro sea eliminado
                    LogActividad::registrar(
                        'Clientes', // Tipo de actividad
                        'Eliminó al cliente: ' . $this->record->nombre . ' ' . $this->record->apellido, // Mensaje
                        [ // Metadata adicional
                            'cliente_id' => $this->record->id_cliente,
                            'documento' => $this->record->numero_documento,
                            'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                            'datos_eliminados' => $this->record->toArray() // Guarda una copia de los datos del cliente eliminado
                        ]
                    );
                })
                ->after(function () {
                    // Opcional: Notificación de éxito después de eliminar
                    Notification::make()
                        ->title('Cliente eliminado exitosamente')
                        ->success()
                        ->send();
                }),
        ];
    }
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
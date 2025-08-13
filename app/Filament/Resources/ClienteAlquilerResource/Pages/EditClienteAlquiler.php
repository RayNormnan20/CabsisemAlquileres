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
        LogActividad::registrar(
            'ClienteAlquiler',
            'Actualizó los datos del cliente de alquiler: ' . $this->record->nombre . ' ' . $this->record->apellido,
            [
                'cliente_alquiler_id' => $this->record->id_cliente_alquiler,
                'documento' => $this->record->numero_documento,
                'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                'cambios_realizados' => $this->record->getChanges(),
            ]
        );

        Notification::make()
            ->title('Cliente de alquiler actualizado exitosamente')
            ->success()
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    LogActividad::registrar(
                        'ClienteAlquiler',
                        'Eliminó al cliente de alquiler: ' . $this->record->nombre . ' ' . $this->record->apellido,
                        [
                            'cliente_alquiler_id' => $this->record->id_cliente_alquiler,
                            'documento' => $this->record->numero_documento,
                            'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                            'datos_eliminados' => $this->record->toArray()
                        ]
                    );
                })
                ->after(function () {
                    Notification::make()
                        ->title('Cliente de alquiler eliminado exitosamente')
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

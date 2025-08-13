<?php

namespace App\Filament\Resources\ClienteAlquilerResource\Pages;

use App\Filament\Resources\ClienteAlquilerResource;
use App\Models\LogActividad;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateClienteAlquiler extends CreateRecord
{
    protected static string $resource = ClienteAlquilerResource::class;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar la ruta de la sesión automáticamente
        if ($this->currentRutaId) {
            $data['id_ruta'] = $this->currentRutaId;
        } else {
            throw new \Exception('No hay una ruta seleccionada en la sesión.');
        }

        // Asignar el usuario creador
        $data['id_usuario_creador'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'ClienteAlquiler',
            'Registró un nuevo cliente de alquiler',
            [
                'cliente_id' => $this->record->id_cliente_alquiler,
                'documento' => $this->record->numero_documento,
                'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                'ruta_id' => $this->record->id_ruta
            ]
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Cliente de alquiler registrado exitosamente';
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'El cliente de alquiler ha sido registrado en la ruta seleccionada.';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}

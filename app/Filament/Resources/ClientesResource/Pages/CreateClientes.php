<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use App\Filament\Resources\CreditosResource;
use App\Models\LogActividad;
use App\Events\ClienteCreated;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateClientes extends CreateRecord
{
    protected static string $resource = ClientesResource::class;

    public bool $crearCredito = false;

    public ?string $returnTo = null;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();

        $this->currentRutaId = Session::get('selected_ruta_id');

        // Recordar a dónde volver tras guardar (p.ej. 'yape-clientes-create')
        $this->returnTo = request()->query('return_to');

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
        $this->crearCredito = $data['crear_credito'] ?? false;
        unset($data['crear_credito']);

        if ($this->currentRutaId) {
            $data['id_ruta'] = $this->currentRutaId;
        } else {
            // Puedes lanzar una excepción o registrar un error aquí si es obligatorio
            throw new \Exception('El usuario autenticado no tiene una ruta asignada.');
        }

        // Asignar también el usuario creador si lo necesitas
        $data['id_usuario_creador'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Disparar evento de cliente creado para WebSocket
        event(new ClienteCreated($this->record));
        
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Clientes',
            'Registró un nuevo cliente',
            [
                'cliente_id' => $this->record->id_cliente,
                'documento' => $this->record->numero_documento,
                'nombre_completo' => $this->record->nombre . ' ' . $this->record->apellido,
                'negocio' => $this->record->nombre_negocio ?? 'Sin negocio'
            ]
        );
    }

    protected function getRedirectUrl(): string
    {
        if ($this->crearCredito) {
            return CreditosResource::getUrl('create', [
                'cliente_id' => $this->record->id_cliente
            ]);
        }

        // Si se indicó volver a Crear Yape Cliente, redirigir ahí
        if ($this->returnTo === 'yape-clientes-create') {
            return \App\Filament\Resources\YapeClienteResource::getUrl('create', [
                'cliente_id' => $this->record->id_cliente,
            ]);
        }

        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->crearCredito
            ? 'Cliente registrado. Redirigiendo a creación de crédito...'
            : 'Cliente registrado exitosamente';
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return $this->crearCredito
            ? 'El cliente se ha registrado correctamente. Ahora puedes crear su crédito.'
            : 'El cliente ha sido registrado en el sistema.';
    }
}
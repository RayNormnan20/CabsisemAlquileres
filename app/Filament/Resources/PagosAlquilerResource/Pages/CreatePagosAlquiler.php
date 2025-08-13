<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreatePagosAlquiler extends CreateRecord
{
    protected static string $resource = PagosAlquilerResource::class;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario que registra el pago automáticamente
        $data['id_usuario_registro'] = auth()->id();

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pago de alquiler registrado exitosamente';
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'El pago de alquiler ha sido registrado correctamente.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

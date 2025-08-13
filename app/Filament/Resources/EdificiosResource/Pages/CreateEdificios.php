<?php

namespace App\Filament\Resources\EdificiosResource\Pages;

use App\Filament\Resources\EdificiosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateEdificios extends CreateRecord
{
    protected static string $resource = EdificiosResource::class;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario_creador'] = auth()->id();

        // Asignar automáticamente la ruta de la sesión
        if (Session::has('selected_ruta_id')) {
            $data['id_ruta'] = Session::get('selected_ruta_id');
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Edificio registrado exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}

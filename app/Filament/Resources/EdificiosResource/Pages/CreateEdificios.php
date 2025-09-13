<?php

namespace App\Filament\Resources\EdificiosResource\Pages;

use App\Filament\Resources\EdificiosResource;
use App\Models\LogActividad;
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

    protected function afterCreate(): void
    {
        // Registrar log de actividad
        LogActividad::create([
            'user_id' => auth()->id(),
            'tipo' => 'Edificios',
            'mensaje' => 'Edificio creado: ' . $this->record->nombre,
            'metadata' => [
                'accion' => 'crear',
                'id_edificio' => $this->record->id_edificio,
                'nombre' => $this->record->nombre,
                'direccion' => $this->record->direccion,
                'ciudad' => $this->record->ciudad,
                'numero_pisos' => $this->record->numero_pisos,
                'id_cliente_alquiler' => $this->record->id_cliente_alquiler,
                'id_ruta' => $this->record->id_ruta,
                'activo' => $this->record->activo,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ]);
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
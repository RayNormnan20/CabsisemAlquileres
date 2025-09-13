<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateDepartamentos extends CreateRecord
{
    protected static string $resource = DepartamentosResource::class;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario creador
        $data['id_usuario_creador'] = auth()->id();

        // Asignar automáticamente la ruta de la sesión
        if (Session::has('selected_ruta_id')) {
            $data['id_ruta'] = Session::get('selected_ruta_id');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Departamentos',
            'Registró un nuevo departamento: ' . $this->record->numero_departamento . ' en el edificio ' . $this->record->edificio->nombre,
            [
                'departamento_id' => $this->record->id_departamento,
                'numero_departamento' => $this->record->numero_departamento,
                'piso' => $this->record->piso,
                'edificio_id' => $this->record->id_edificio,
                'edificio_nombre' => $this->record->edificio->nombre,
                'estado_departamento_id' => $this->record->id_estado_departamento,
                'precio_alquiler' => $this->record->precio_alquiler,
                'ruta_id' => $this->record->id_ruta,
                'activo' => $this->record->activo
            ]
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Departamento registrado exitosamente';
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

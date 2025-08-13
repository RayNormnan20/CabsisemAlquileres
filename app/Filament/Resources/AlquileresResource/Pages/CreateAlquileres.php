<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use App\Models\Departamento;
use App\Models\EstadoDepartamento;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;

class CreateAlquileres extends CreateRecord
{
    protected static string $resource = AlquileresResource::class;

    public ?int $currentRutaId = null;

    public function mount(): void
    {
        parent::mount();
        $this->currentRutaId = Session::get('selected_ruta_id');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario creador automáticamente
        $data['id_usuario_creador'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Cambiar el estado del departamento a "Ocupado" cuando se crea un alquiler
        $estadoOcupado = EstadoDepartamento::where('nombre', 'Ocupado')
            ->where('activo', true)
            ->first();

        if ($estadoOcupado) {
            Departamento::where('id_departamento', $this->record->id_departamento)
                ->update(['id_estado_departamento' => $estadoOcupado->id_estado_departamento]);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Alquiler registrado exitosamente';
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'El alquiler ha sido registrado correctamente y el departamento ha sido marcado como ocupado.';
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

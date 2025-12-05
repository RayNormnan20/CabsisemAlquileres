<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use App\Models\Departamento;
use App\Models\EstadoDepartamento;
use App\Models\LogActividad;
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
        $prefillDepartamento = request()->get('prefillDepartamento');
        if ($prefillDepartamento) {
            $this->form->fill(['id_departamento' => (int) $prefillDepartamento]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario creador automáticamente
        $data['id_usuario_creador'] = auth()->id();

        // Si la fecha de inicio es hoy, establecer la fecha de próximo pago un mes después de la fecha de inicio
        if (isset($data['fecha_inicio'])) {
            $fechaInicio = \Carbon\Carbon::parse($data['fecha_inicio']);
            $hoy = \Carbon\Carbon::today();
            
            if ($fechaInicio->isSameDay($hoy)) {
                $data['fecha_proximo_pago'] = $fechaInicio->copy()->addMonth();
            }
        }

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

        // Registrar la actividad en el log
        LogActividad::registrar(
            'Alquileres',
            'Registró un nuevo alquiler para el departamento ' . $this->record->departamento->numero_departamento . ' del edificio ' . ($this->record->departamento->edificio ? $this->record->departamento->edificio->nombre : 'Sin Edificio'),
            [
                'alquiler_id' => $this->record->id_alquiler,
                'departamento_id' => $this->record->id_departamento,
                'departamento_numero' => $this->record->departamento->numero_departamento,
                'edificio_nombre' => $this->record->departamento->edificio ? $this->record->departamento->edificio->nombre : 'Sin Edificio',
                'inquilino_id' => $this->record->id_cliente_alquiler,
                'inquilino_nombre' => $this->record->inquilino->nombre_completo ?? 'Sin inquilino',
                'precio_mensual' => $this->record->precio_mensual,
                'fecha_inicio' => $this->record->fecha_inicio->format('Y-m-d'),
                'estado_alquiler' => $this->record->estado_alquiler
            ]
        );
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

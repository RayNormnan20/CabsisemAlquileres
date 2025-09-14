<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use App\Models\LogActividad;
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

    protected function afterCreate(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Pagos Alquiler',
            'Registró un nuevo pago de alquiler por S/. ' . number_format($this->record->monto_pagado, 2) . ' para el departamento ' . $this->record->alquiler->departamento->numero_departamento,
            [
                'pago_id' => $this->record->id_pago_alquiler,
                'alquiler_id' => $this->record->id_alquiler,
                'departamento_numero' => $this->record->alquiler->departamento->numero_departamento,
                'edificio_nombre' => $this->record->alquiler->departamento->edificio ? $this->record->alquiler->departamento->edificio->nombre : 'Sin Edificio',
                'inquilino_nombre' => $this->record->alquiler->inquilino->nombre_completo ?? 'Sin inquilino',
                'monto_pagado' => $this->record->monto_pagado,
                'fecha_pago' => $this->record->fecha_pago->format('Y-m-d'),
                'mes_correspondiente' => $this->record->mes_correspondiente,
                'ano_correspondiente' => $this->record->ano_correspondiente
            ]
        );
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
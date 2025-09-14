<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagosAlquiler extends EditRecord
{
    protected static string $resource = PagosAlquilerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Registrar la eliminación en el log
                    LogActividad::registrar(
                        'Pagos Alquiler',
                        'Eliminó el pago de alquiler por S/.' . number_format($this->record->monto_pagado, 2) . ' del departamento ' . $this->record->alquiler->departamento->numero_departamento,
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
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Registrar la edición en el log
        LogActividad::registrar(
            'Pagos Alquiler',
            'Editó el pago de alquiler por S/. ' . number_format($this->record->monto_pagado, 2) . ' del departamento ' . $this->record->alquiler->departamento->numero_departamento,
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
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

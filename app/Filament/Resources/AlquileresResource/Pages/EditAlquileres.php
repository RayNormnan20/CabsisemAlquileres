<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use App\Models\EstadoDepartamento;
use App\Models\Departamento;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlquileres extends EditRecord
{
    protected static string $resource = AlquileresResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Registrar la eliminación en el log
                    LogActividad::registrar(
                        'Alquileres',
                        'Eliminó el alquiler del departamento ' . $this->record->departamento->numero_departamento . ' del edificio ' . ($this->record->departamento->edificio ? $this->record->departamento->edificio->nombre : 'Sin Edificio'),
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
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Si el estado del alquiler cambió a 'finalizado' y la fecha de inicio es hoy,
        // establecer la fecha de próximo pago un mes después de la fecha de inicio
        if ($this->record->estado_alquiler === 'finalizado') {
            $fechaInicio = \Carbon\Carbon::parse($this->record->fecha_inicio);
            $hoy = \Carbon\Carbon::today();

            if ($fechaInicio->isSameDay($hoy)) {
                $this->record->update([
                    'fecha_proximo_pago' => $fechaInicio->copy()->addMonth()
                ]);
            }

            // Al finalizar el alquiler, cambiar el estado del departamento a 'Disponible'
            $estadoDisponible = EstadoDepartamento::where('nombre', 'Disponible')
                ->where('activo', true)
                ->first();

            if ($estadoDisponible) {
                $departamento = $this->record->departamento;
                if ($departamento) {
                    $departamento->id_estado_departamento = $estadoDisponible->id_estado_departamento;
                    $departamento->save();
                }
            }
        }

        // Registrar la edición en el log
        LogActividad::registrar(
            'Alquileres',
            'Editó el alquiler del departamento ' . $this->record->departamento->numero_departamento . ' del edificio ' . ($this->record->departamento->edificio ? $this->record->departamento->edificio->nombre : 'Sin Edificio'),
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
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

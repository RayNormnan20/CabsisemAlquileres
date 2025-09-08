<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Models\LogActividad;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCreditos extends EditRecord
{
    protected static string $resource = CreditosResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar el nombre_yape desde el YapeCliente asociado si existe
        if ($this->record && $this->record->yapeCliente) {
            $data['nombre_yape'] = $this->record->yapeCliente->nombre;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        // Intentar múltiples métodos para obtener los datos del repeater
        $conceptosCredito = null;

        // Método 1: getRawState()
        $rawData = $this->form->getRawState();
        if (isset($rawData['conceptosCredito'])) {
            $conceptosCredito = $rawData['conceptosCredito'];
        }

        // Método 2: getState() directo
        if (!$conceptosCredito && isset($data['conceptosCredito'])) {
            $conceptosCredito = $data['conceptosCredito'];
        }

        // Método 3: Acceso directo al componente
        if (!$conceptosCredito) {
            try {
                $repeaterComponent = $this->form->getComponent('conceptosCredito');
                if ($repeaterComponent) {
                    $conceptosCredito = $repeaterComponent->getState();
                }
            } catch (\Exception $e) {
            }
        }

        // Método 4: Desde livewire state
        if (!$conceptosCredito) {
            $conceptosCredito = $this->data['conceptosCredito'] ?? null;
        }

        if ($conceptosCredito) {
            $data['conceptosCredito'] = $conceptosCredito;
        }

        $this->validateCreditSum($data);
    }

    protected function validateCreditSum(array $data): void
    {
        // Debug adicional: mostrar estructura completa
        file_put_contents(storage_path('logs/debug_credito_edit.json'), json_encode($data, JSON_PRETTY_PRINT));

        $valorCredito = (float) ($data['valor_credito'] ?? 0);
        // Verificar diferentes posibles nombres del campo
        $conceptos = $data['conceptosCredito'] ?? $data['conceptos_credito'] ?? $data['conceptos'] ?? [];
        $sumaConceptos = 0;
        foreach ($conceptos as $index => $concepto) {
            $monto = (float) ($concepto['monto'] ?? 0);
            $sumaConceptos += $monto;
        }
        $diferencia = abs($sumaConceptos - $valorCredito);
        if ($diferencia > 0.01) {
            if ($sumaConceptos < $valorCredito) {
                \Filament\Notifications\Notification::make()
                    ->title('Error de Validación')
                    ->body("La suma de los montos (S/ {$sumaConceptos}) es menor al valor del crédito (S/ {$valorCredito}). Diferencia: S/ " . number_format($valorCredito - $sumaConceptos, 2))
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Error de Validación')
                    ->body("La suma de los montos (S/ {$sumaConceptos}) es mayor al valor del crédito (S/ {$valorCredito}). Diferencia: S/ " . number_format($sumaConceptos - $valorCredito, 2))
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // La validación se movió al método beforeSave()
        return $data;
    }

    protected function afterSave(): void
    {
        // Manejar YapeCliente cuando se edita el crédito
        if (isset($this->data['nombre_yape'])) {
            $nombreYape = $this->data['nombre_yape'];

            // Verificar si hay conceptos de tipo Yape en el crédito
            $this->record->load('conceptosCredito');
            $tieneConceptoYape = $this->record->conceptosCredito->where('tipo_concepto', 'Yape')->isNotEmpty();

            if ($tieneConceptoYape) {
                if ($this->record->yapeCliente) {
                    // Si ya existe un YapeCliente asociado, actualizar el nombre
                    $this->record->yapeCliente->update([
                        'nombre' => $nombreYape
                    ]);
                } else {
                    // Si no existe YapeCliente asociado, crear uno nuevo
                    $conceptoYape = $this->record->conceptosCredito->where('tipo_concepto', 'Yape')->first();

                    // Verificar si ya existe un YapeCliente con el mismo nombre y cliente sin id_credito
                    $yapeExistente = \App\Models\YapeCliente::where('id_cliente', $this->record->id_cliente)
                        ->where('nombre', $nombreYape)
                        ->whereNull('id_credito')
                        ->first();

                    if ($yapeExistente) {
                        // Actualizar el registro existente con el id_credito
                        $yapeExistente->update([
                            'id_credito' => $this->record->id_credito,
                            'monto' => $conceptoYape->monto,
                            'valor' => $this->record->valor_credito,
                            'entregar' => 0,
                            'user_id' => auth()->id(),
                        ]);
                    } else {
                        // Crear un nuevo registro
                        \App\Models\YapeCliente::create([
                            'id_cliente' => $this->record->id_cliente,
                            'id_credito' => $this->record->id_credito,
                            'nombre' => $nombreYape,
                            'user_id' => auth()->id(),
                            'monto' => $conceptoYape->monto,
                            'valor' => $this->record->valor_credito,
                            'entregar' => 0,
                        ]);
                    }
                }
            }
        }

        $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
        $rutaNombre = $this->record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

        $changes = $this->record->getChanges();
        $relevantChanges = [];
        $fieldsToLog = ['fecha_credito', 'valor_credito', 'porcentaje_interes'];

        foreach ($fieldsToLog as $field) {
            if (array_key_exists($field, $changes)) {
                if ($field === 'fecha_credito') {
                    $relevantChanges[$field] = $this->record->$field->format('Y-m-d');
                } else {
                    $relevantChanges[$field] = $changes[$field];
                }
            }
        }

        LogActividad::registrar(
            'Créditos',
            "Editó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
            $relevantChanges
        );

        Notification::make()
            ->title('Crédito actualizado exitosamente')
            ->success()
            ->send();
    }

   protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->abonos()->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar el crédito')
                            ->body('Este crédito tiene abonos realizados y no puede ser eliminado.')
                            ->danger()
                            ->send();
                        throw new \Exception('El crédito tiene abonos realizados.');
                    }

                    // Eliminar el YapeCliente asociado si existe
                    if ($this->record->yapeCliente) {
                        $this->record->yapeCliente->forceDelete();
                    }

                    $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
                    $rutaNombre = $this->record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

                    LogActividad::registrar(
                        'Créditos',
                        "Eliminó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
                        [
                            'credito_id' => $this->record->id_credito,
                            'cliente_id' => $this->record->id_cliente,
                            'datos_eliminados' => $this->record->toArray(),
                        ]
                    );
                })
                ->after(function () {
                    Notification::make()
                        ->title('Crédito eliminado exitosamente')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

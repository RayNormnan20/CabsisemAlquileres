<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Models\ConceptoCredito;
use App\Models\LogActividad;
use App\Events\CreditoDeleted;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCreditos extends EditRecord
{
    protected static string $resource = CreditosResource::class;
    
    protected $valorCreditoOriginal;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los nombres_yape desde los YapeCliente asociados (múltiples)
        if ($this->record) {
            $yapes = \App\Models\YapeCliente::where('id_credito', $this->record->id_credito)
                ->whereNotNull('nombre')
                ->where('nombre', '!=', '')
                ->get(['nombre','monto']);

            $nombresYape = $yapes->pluck('nombre')->toArray();

            // Prefill distribución en edición (KeyValue)
            $data['distribucion_yape'] = $yapes->reduce(function ($carry, $yc) {
                $carry[$yc->nombre] = (float) $yc->monto;
                return $carry;
            }, []);

            if ($this->record->es_adicional) {
                $data['nombre_yape_adicional'] = $nombresYape;
            } else {
                $data['nombre_yape'] = $nombresYape;
            }
        }

        // Cargar los conceptos del crédito para el repeater
        if ($this->record) {
            $this->record->load('conceptosCredito');
            $conceptos = [];

            foreach ($this->record->conceptosCredito as $concepto) {
                $conceptos[] = [
                    'tipo_concepto' => $concepto->tipo_concepto,
                    'monto' => $concepto->monto,
                    'referencia' => $concepto->referencia,
                    'foto_comprobante' => $concepto->foto_comprobante,
                ];
            }

            $data['conceptosCredito'] = $conceptos;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        // Guardar el valor original del crédito para créditos adicionales
        if ($this->record->es_adicional) {
            $this->valorCreditoOriginal = $this->record->valor_credito;
        }

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

        // Validación estricta de distribución Yape al editar
        $this->validateYapeDistributionStrictOnEdit($data);
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

    protected function validateYapeDistributionStrictOnEdit(array $data): void
    {
        // Buscar monto del concepto Yape dentro de los conceptos del formulario
        $conceptos = $data['conceptosCredito'] ?? $data['conceptos_credito'] ?? $data['conceptos'] ?? [];
        $yapeMonto = 0.0;
        foreach ($conceptos as $c) {
            if (($c['tipo_concepto'] ?? '') === 'Yape') {
                $yapeMonto = (float) ($c['monto'] ?? 0);
                break;
            }
        }

        // Determinar nombres seleccionados
        $nombresYapeSeleccionados = [];
        if (!empty($data['es_adicional']) && isset($data['nombre_yape_adicional'])) {
            $nombresYapeSeleccionados = $data['nombre_yape_adicional'];
        } elseif (isset($data['nombre_yape'])) {
            $nombresYapeSeleccionados = $data['nombre_yape'];
        }
        if (is_string($nombresYapeSeleccionados)) {
            $nombresYapeSeleccionados = [$nombresYapeSeleccionados];
        }

        if (is_array($nombresYapeSeleccionados) && count($nombresYapeSeleccionados) > 1) {
            $dist = $data['distribucion_yape'] ?? [];
            $suma = 0.0;
            if (is_array($dist)) {
                foreach ($dist as $monto) {
                    $suma += (float) $monto;
                }
            }
            if (empty($dist) || abs($suma - $yapeMonto) > 0.01) {
                \Filament\Notifications\Notification::make()
                    ->title('Error de Distribución Yape')
                    ->body('La suma asignada por Nombre Yape debe ser igual al monto del concepto Yape (S/ ' . number_format($yapeMonto, 2) . ').')
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }
        }
    }

    protected function afterSave(): void
    {
        // Actualizar los conceptos del crédito
        if (isset($this->data['conceptosCredito'])) {
            // Eliminar conceptos existentes
            $this->record->conceptosCredito()->delete();

            // Crear nuevos conceptos
            foreach ($this->data['conceptosCredito'] as $conceptoData) {
                \App\Models\ConceptoCredito::create([
                    'id_credito' => $this->record->id_credito,
                    'tipo_concepto' => $conceptoData['tipo_concepto'] ?? null,
                    'monto' => $conceptoData['monto'] ?? 0,
                    'foto_comprobante' => $this->procesarFotoComprobante($conceptoData['foto_comprobante'] ?? null),
                ]);
            }

            // Recargar la relación
            $this->record->load('conceptosCredito');
        }

        // Manejar YapeCliente cuando se edita el crédito (múltiples nombres)
        $nombresYapeSeleccionados = [];
        if ($this->record->es_adicional && isset($this->data['nombre_yape_adicional'])) {
            $nombresYapeSeleccionados = $this->data['nombre_yape_adicional'];
        } elseif (!$this->record->es_adicional && isset($this->data['nombre_yape'])) {
            $nombresYapeSeleccionados = $this->data['nombre_yape'];
        }
        if (is_string($nombresYapeSeleccionados)) {
            $nombresYapeSeleccionados = [$nombresYapeSeleccionados];
        }

        if (!empty($nombresYapeSeleccionados)) {
            // Verificar si hay conceptos de tipo Yape en el crédito
            $this->record->load('conceptosCredito');
            $tieneConceptoYape = $this->record->conceptosCredito->where('tipo_concepto', 'Yape')->isNotEmpty();

            if ($tieneConceptoYape) {
                $conceptoYape = $this->record->conceptosCredito->where('tipo_concepto', 'Yape')->first();

                // Mapear distribución desde el formulario si existe (KeyValue nombre=>monto)
                $distribucion = $this->data['distribucion_yape'] ?? [];
                $mapeoDistribucion = [];
                if (is_array($distribucion)) {
                    foreach ($distribucion as $nombre => $monto) {
                        if (is_string($nombre)) {
                            $mapeoDistribucion[$nombre] = (float) $monto;
                        }
                    }
                }

                // El primero mantiene el monto; los restantes se guardan con monto 0
                foreach ($nombresYapeSeleccionados as $index => $nombreYape) {
                    if (!empty($mapeoDistribucion) && count($nombresYapeSeleccionados) > 1) {
                        $montoAsignado = (float) ($mapeoDistribucion[$nombreYape] ?? 0);
                    } else {
                        $montoAsignado = (count($nombresYapeSeleccionados) === 1)
                            ? (float) ($conceptoYape->monto ?? 0)
                            : ($index === 0 ? (float) ($conceptoYape->monto ?? 0) : 0);
                    }

                    // Buscar si ya existe un YapeCliente de este crédito con ese nombre
                    $yapeDelCredito = \App\Models\YapeCliente::where('id_credito', $this->record->id_credito)
                        ->where('nombre', $nombreYape)
                        ->first();

                    if ($yapeDelCredito) {
                        $yapeDelCredito->update([
                            'monto' => $montoAsignado,
                            'valor' => $this->record->valor_credito
                        ]);
                    } else {
                        // Si no existe, ver si hay un YapeCliente pendiente (sin crédito) con ese nombre
                        $yapeExistente = \App\Models\YapeCliente::where('id_cliente', $this->record->id_cliente)
                            ->where('nombre', $nombreYape)
                            ->whereNull('id_credito')
                            ->first();

                        if ($yapeExistente) {
                            $yapeExistente->update([
                                'id_credito' => $this->record->id_credito,
                                'monto' => $montoAsignado,
                                'valor' => $this->record->valor_credito,
                                'entregar' => 0,
                                'user_id' => auth()->id(),
                            ]);
                        } else {
                            \App\Models\YapeCliente::create([
                                'id_cliente' => $this->record->id_cliente,
                                'id_credito' => $this->record->id_credito,
                                'nombre' => $nombreYape,
                                'user_id' => auth()->id(),
                                'monto' => $montoAsignado,
                                'valor' => $this->record->valor_credito,
                                'entregar' => 0,
                            ]);
                        }
                    }
                }

                // Eliminar YapeClientes del crédito que no estén en la selección actual
                \App\Models\YapeCliente::where('id_credito', $this->record->id_credito)
                    ->whereNotIn('nombre', $nombresYapeSeleccionados)
                    ->delete();
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

        // Actualizar saldo para créditos adicionales si cambió el valor del crédito
        if ($this->record->es_adicional && isset($this->valorCreditoOriginal)) {
            $valorAnterior = $this->valorCreditoOriginal;
            $valorNuevo = $this->record->valor_credito;
            
            if ($valorAnterior != $valorNuevo) {
                // Para créditos adicionales, calcular cuánto se ha abonado hasta ahora
                $totalAbonos = $this->record->abonos()->sum('monto_abono');
                
                // Calcular las cuotas diarias ya aplicadas (saldo actual - valor original + abonos)
                $cuotasDiariasAplicadas = $this->record->saldo_actual - $valorAnterior + $totalAbonos;
                
                // El nuevo saldo debe ser: nuevo valor + cuotas diarias aplicadas - abonos realizados
                // Esto preserva las cuotas diarias ya aplicadas al crédito
                $this->record->saldo_actual = $valorNuevo + $cuotasDiariasAplicadas - $totalAbonos;
                $this->record->save();
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

        try {
            \Illuminate\Support\Facades\Session::forget('creditos_cliente_id');
            \Illuminate\Support\Facades\Session::forget('abonos_cliente_id');
            \Illuminate\Support\Facades\Session::forget('abonos_mostrar_creditos');
            \Illuminate\Support\Facades\Session::forget('creditos_mostrar_solo_activos');
        } catch (\Throwable $e) {}
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

                    // Disparar evento para actualización en tiempo real
                    try {
                        $record = $this->record;
                        $idRuta = $record->id_ruta;
                        if (!$idRuta && $record->cliente) {
                            $idRuta = $record->cliente->id_ruta;
                        }

                        if ($idRuta) {
                            event(new CreditoDeleted($idRuta, $record->toArray()));
                        }
                    } catch (\Throwable $e) {}
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        if (session('return_to_credito_view') && session('credito_id_return')) {
            $creditoId = session('credito_id_return');
            session()->forget(['return_to_credito_view', 'credito_id_return']);
            return CreditosResource::getUrl('view', ['record' => $creditoId]);
        }

        return $this->getResource()::getUrl('index');
    }

    private function procesarFotoComprobante($fotoComprobante)
    {
        // Si está vacío, retornar null
        if (empty($fotoComprobante)) {
            return null;
        }

        // Si es un string, ya es la ruta del archivo
        if (is_string($fotoComprobante)) {
            return $fotoComprobante;
        }

        // Si es un array, tomar el primer elemento válido
        if (is_array($fotoComprobante)) {
            // Si el array está vacío, retornar null
            if (empty($fotoComprobante)) {
                return null;
            }

            // Buscar el primer elemento que sea string
            foreach ($fotoComprobante as $elemento) {
                if (is_string($elemento) && !empty($elemento)) {
                    return $elemento;
                }
            }
        }

        // Si no es string ni array válido, retornar null
        return null;
    }
}

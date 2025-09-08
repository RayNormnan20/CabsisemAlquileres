<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Helpers\FechaHelper;
use App\Models\Concepto;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use App\Models\LogActividad;
use App\Models\TipoPago;
use App\Models\YapeCliente;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreateCreditos extends CreateRecord
{

    public ?int $currentRutaId = null;
    protected static string $resource = CreditosResource::class;

    protected function validateCreditSum(array $data): void
    {

        // Debug adicional: mostrar estructura completa
        file_put_contents(storage_path('logs/debug_credito.json'), json_encode($data, JSON_PRETTY_PRINT));
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

    protected function validateCreditSumBeforeCreate(array $data): void
    {
        // Debug adicional: mostrar estructura completa
        file_put_contents(storage_path('logs/debug_credito_create.json'), json_encode($data, JSON_PRETTY_PRINT));
        $valorCredito = (float) ($data['valor_credito'] ?? 0);

        // Verificar diferentes posibles nombres del campo
        $conceptos = $data['conceptosCredito'] ?? $data['conceptos_credito'] ?? $data['conceptos'] ?? [];
        $sumaConceptos = 0;
        foreach ($conceptos as $index => $concepto) {
            $monto = (float) ($concepto['monto'] ?? 0);
            $sumaConceptos += $monto;

        }
      $diferencia = abs($sumaConceptos - $valorCredito); if ($diferencia > 0.01) {
         if ($sumaConceptos < $valorCredito) {
             \Filament\Notifications\Notification::make()
             ->title('Error de Validación')
             ->body("La suma de los montos es menor al valor del crédito (S/ {$valorCredito}). Diferencia: S/ " . number_format($valorCredito - $sumaConceptos, 2))
             ->danger() ->duration(5000) ->send(); $this->halt();
            } else {
                 \Filament\Notifications\Notification::make()
                 ->title('Error de Validación') ->body("La suma de los montos (S/ {$sumaConceptos}) es mayor al valor del crédito (S/ {$valorCredito}). Diferencia: S/ " . number_format($sumaConceptos - $valorCredito, 2))
                 ->danger()
                 ->duration(5000)
                 ->send();
                 $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Primero ejecutar la validación de suma de conceptos
        $this->validateCreditSumBeforeCreate($data);

        $tipoCredito = $data['es_adicional'] ?? false;

        if ($tipoCredito) {
            if (empty($data['valor_credito']) || empty($data['porcentaje_interes'])) {
                throw new \Exception('Para créditos adicionales, el valor del crédito y la cuota diaria son obligatorios.');
            }
        } else {
            if (
                empty($data['valor_credito']) || empty($data['porcentaje_interes']) ||
                empty($data['dias_plazo']) || empty($data['forma_pago'])
            ) {
                throw new \Exception('Todos los campos requeridos deben estar completos.');
            }
        }

        if ($tipoCredito) {
            $cuotaDiaria = (float) $data['porcentaje_interes'];
            $valorCredito = (float) $data['valor_credito'];

            $data['saldo_actual'] = $valorCredito;
            $data['porcentaje_interes'] = $cuotaDiaria;

            $data['dias_plazo'] = 0; // no aplica
            $data['numero_cuotas'] = 0; // no aplica
            $data['valor_cuota'] = 0; // no aplica
            $data['es_adicional'] = true;

            // Proximo día laborable desde mañana
            $data['fecha_proximo_pago'] = FechaHelper::siguienteDiaLaborable(
                Carbon::parse($data['fecha_credito'])->addDay()
            )->format('Y-m-d');

            $data['fecha_vencimiento'] = Carbon::parse('2099-12-31');

            $data['forma_pago'] = TipoPago::where('nombre', 'Diario')->value('id_forma_pago');

            $conceptoAdicional = Concepto::where('nombre', 'Adicional')->first();

            if (!$conceptoAdicional) {
                throw new \Exception('El concepto "Adicional" no existe en la tabla de conceptos.');
            }

            $data['id_concepto'] = $conceptoAdicional->id;
        } else {

            $valorCredito = (float)$data['valor_credito'];
            $porcentaje = (float)$data['porcentaje_interes'];
            $dias = (int)$data['dias_plazo'];
            $formaPago = TipoPago::find($data['forma_pago'])->nombre;

            $data['es_adicional'] = false;

            // Calcular valor total con interés
            $valorTotal = $valorCredito * (1 + ($porcentaje / 100));
            $data['saldo_actual'] = $valorTotal;

            // Calcular número de cuotas
            switch (strtolower($formaPago)) {
                case 'diario':
                    $numeroCuotas = $dias;
                    break;
                case 'semanal':
                    $numeroCuotas = ceil($dias / 7);
                    break;
                case 'quincenal':
                    $numeroCuotas = ceil($dias / 15);
                    break;
                case 'mensual':
                    $numeroCuotas = ceil($dias / 30);
                    break;
                default:
                    $numeroCuotas = $dias;
            }

            $numeroCuotas = max(1, $numeroCuotas);

            $data['valor_cuota'] = $valorTotal / $numeroCuotas;
            $data['numero_cuotas'] = $numeroCuotas;

            $fechaCredito = Carbon::parse($data['fecha_credito']);
            $data['fecha_vencimiento'] = $fechaCredito->copy()->addDays($dias)->format('Y-m-d');

            // Calcular próxima fecha de pago
            switch (strtolower($formaPago)) {
                case 'diario':
                    $next = $fechaCredito->addDay();
                    break;
                case 'semanal':
                    $next = $fechaCredito->addWeek();
                    break;
                case 'quincenal':
                    $next = $fechaCredito->addDays(15);
                    break;
                case 'mensual':
                    $next = $fechaCredito->addMonth();
                    break;
                default:
                    $next = $fechaCredito->addDay();
            }

            $data['fecha_proximo_pago'] = $next->format('Y-m-d');

            $concepto = Concepto::where('nombre', 'Desembolso')->first();

            if (!$concepto) {
                throw new \Exception('El concepto "Desembolso" no existe en la tabla de conceptos.');
            }



            $data['id_concepto'] = $concepto->id;
        }

        $this->currentRutaId = Session::get('selected_ruta_id');
        if (!$this->currentRutaId) {
            throw new \Exception('El usuario no tiene una ruta asignada.');
        }
        $data['id_ruta'] = $this->currentRutaId;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Solo crear el crédito principal, sin manejar yape_clientes aquí
            return parent::handleRecordCreation($data);
        } catch (\DivisionByZeroError $e) {
            throw new \Exception('Error en los cálculos: Verifique los valores ingresados');
        } catch (\Exception $e) {
            throw new \Exception('Error al crear el crédito: ' . $e->getMessage());
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Crédito creado exitosamente';
    }

    protected function getRedirectUrl(): string
    {
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

    protected function afterCreate(): void
    {
        // Guardar los conceptos del repeater manualmente ya que no usamos relationship()
        $conceptosData = $this->data['conceptosCredito'] ?? [];

        foreach ($conceptosData as $conceptoData) {
            \App\Models\ConceptoCredito::create([
                'id_credito' => $this->record->id_credito,
                'tipo_concepto' => $conceptoData['tipo_concepto'] ?? null,
                'monto' => $conceptoData['monto'] ?? 0,
                'foto_comprobante' => $this->procesarFotoComprobante($conceptoData['foto_comprobante'] ?? null),
               /// 'user_id' => auth()->id(),
            ]);
        }

        // Recargar la relación después de crear los conceptos
        $this->record->load('conceptosCredito');

        foreach ($this->record->conceptosCredito as $concepto) {
            if ($concepto->tipo_concepto === 'Yape') {
                // Determinar el nombre a usar: nombre_yape o nombre del cliente
                $nombreYape = !empty($this->data['nombre_yape'])
                    ? $this->data['nombre_yape']
                    : $this->record->cliente->nombre_completo;

                // Verificar si ya existe un YapeCliente con el mismo nombre y cliente sin id_credito
                $yapeExistente = YapeCliente::where('id_cliente', $this->record->id_cliente)
                    ->where('nombre', $nombreYape)
                    ->whereNull('id_credito')
                    ->first();

                if ($yapeExistente) {
                    // Actualizar el registro existente con el id_credito
                    $yapeExistente->update([
                        'id_credito' => $this->record->id_credito,
                        'monto' => $concepto->monto,
                        'valor' => $this->record->valor_credito,
                        'entregar' => 0, // Inicializar en 0 para que aparezca en el filtro
                        'user_id' => auth()->id(),
                    ]);
                } else {
                    // Crear un nuevo registro
                    YapeCliente::create([
                        'id_cliente' => $this->record->id_cliente,
                        'id_credito' => $this->record->id_credito,
                        'nombre' => $nombreYape,
                        'user_id' => auth()->id(),
                        'monto' => $concepto->monto,
                        'valor' => $this->record->valor_credito,
                        'entregar' => 0, // Inicializar en 0 para que aparezca en el filtro
                    ]);
                }
                break; // Solo procesar un registro por crédito
            }
        }

        $clienteNombre = $this->record->cliente?->nombre . ' ' . $this->record->cliente?->apellido;
        $rutaNombre = $this->record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

        LogActividad::registrar(
            'Créditos',
            "Registró un nuevo crédito de {$clienteNombre} de la ruta {$rutaNombre}",
            [
                'credito_id' => $this->record->id_credito,
                'cliente_id' => $this->record->id_cliente,
                'valor_credito' => $this->record->valor_credito,
                'saldo_actual' => $this->record->saldo_actual,
                'fecha_credito' => $this->record->fecha_credito->format('Y-m-d'),
            ]
        );
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}

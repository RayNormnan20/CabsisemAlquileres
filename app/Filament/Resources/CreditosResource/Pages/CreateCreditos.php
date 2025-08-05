<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Helpers\FechaHelper;
use App\Models\Concepto;
use App\Models\Creditos;
use App\Models\LogActividad;
use App\Models\TipoPago;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {

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

    protected function afterCreate(): void
    {
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
}

<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Models\Concepto;
use App\Models\Creditos;
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
        // Validaciones básicas
        if (
            empty($data['valor_credito']) || empty($data['porcentaje_interes']) ||
            empty($data['dias_plazo']) || empty($data['forma_pago'])
        ) {
            throw new \Exception('Todos los campos requeridos deben estar completos');
        }

        $this->currentRutaId = Session::get('selected_ruta_id');
        // Obtener la ruta del usuario autenticado
        $user = Auth::user();
        //$ruta = $user->ruta()->first(); // Obtenemos la primera ruta asignada

        if (!$this->currentRutaId) {
            throw new \Exception('El usuario no tiene una ruta asignada.');
        }

        $data['id_ruta'] = $this->currentRutaId;
        // Obtener el concepto "Desembolso" de la tabla conceptos
        $conceptoDesembolso = Concepto::where('nombre', 'Desembolso')->first();

        if (!$conceptoDesembolso) {
            throw new \Exception('El concepto "Desembolso" no existe en la tabla de conceptos');
        }

        // Asignar el id_concepto del desembolso
        $data['id_concepto'] = $conceptoDesembolso->id;

        // Convertir valores a números
        $valorCredito = (float)$data['valor_credito'];
        $porcentaje = (float)$data['porcentaje_interes'];
        $dias = (int)$data['dias_plazo'];
        $formaPago = TipoPago::find($data['forma_pago'])->nombre;

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

    



}

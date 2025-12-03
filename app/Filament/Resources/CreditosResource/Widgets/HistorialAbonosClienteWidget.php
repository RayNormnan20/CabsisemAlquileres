<?php

namespace App\Filament\Resources\CreditosResource\Widgets;

use App\Models\Abonos;
use App\Models\Creditos;
use App\Models\Clientes;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HistorialAbonosClienteWidget extends Widget
{
    protected static string $view = 'filament.resources.creditos-resource.widgets.historial-abonos-cliente-widget';
    
    protected int|string|array $columnSpan = 'full';
    
    public Clientes $record;
    public ?Creditos $creditoEspecifico = null;

    public function mount(): void
    {
        // Obtener el record desde la URL si no está inicializado
        if (!isset($this->record)) {
            $clienteId = request()->route('cliente');
            $creditoId = request()->route('credito');
            
            if ($creditoId) {
                // Si viene un crédito específico, obtenemos el cliente desde el crédito
                $this->creditoEspecifico = Creditos::findOrFail($creditoId);
                $this->record = $this->creditoEspecifico->cliente;
            } elseif ($clienteId) {
                // Si viene un cliente directamente
                $this->record = Clientes::findOrFail($clienteId);
            }
        }
    }

    public function getCreditoActivo()
    {
        if (!isset($this->record) || !$this->record->id_cliente) {
            return null;
        }

        // Si tenemos un crédito específico, usarlo directamente
        if ($this->creditoEspecifico) {
            return $this->creditoEspecifico;
        }

        // Buscar el crédito activo del cliente (con saldo > 0)
        $creditoActivo = Creditos::where('id_cliente', $this->record->id_cliente)
            ->where('saldo_actual', '>', 0)
            ->orderBy('fecha_credito', 'desc')
            ->first();

        // Si no hay crédito activo, buscar el último crédito del cliente
        if (!$creditoActivo) {
            $creditoActivo = Creditos::where('id_cliente', $this->record->id_cliente)
                ->orderBy('fecha_credito', 'desc')
                ->first();
        }

        return $creditoActivo;
    }

    public function getCachedTableQuery(): Builder
    {
        return $this->getTableQuery();
    }

    public function getTableQuery(): Builder
    {
        // Verificar que $record esté inicializado
        if (!isset($this->record) || !$this->record->id_cliente) {
            return Abonos::query()->whereRaw('1 = 0'); // Query que no retorna resultados
        }

        // Si tenemos un crédito específico, usarlo directamente
        if ($this->creditoEspecifico) {
            $creditoActivo = $this->creditoEspecifico;
        } else {
            // Buscar el crédito activo del cliente (con saldo > 0)
            $creditoActivo = Creditos::where('id_cliente', $this->record->id_cliente)
                ->where('saldo_actual', '>', 0)
                ->orderBy('fecha_credito', 'desc')
                ->first();

            // Si no hay crédito activo, buscar el último crédito del cliente
            if (!$creditoActivo) {
                $creditoActivo = Creditos::where('id_cliente', $this->record->id_cliente)
                    ->orderBy('fecha_credito', 'desc')
                    ->first();
            }
        }

        // Si no hay ningún crédito, retornar query vacío
        if (!$creditoActivo) {
            return Abonos::query()->whereRaw('1 = 0'); // Query que no retorna resultados
        }

        // Obtener solo los registros del crédito específico (activo o último)
        $query = Abonos::query()
            ->select([
                'abonos.id_abono',
                'abonos.fecha_pago',
                'abonos.created_at',
                'conceptos.nombre as concepto_nombre',
                'abonos.monto_abono',
                'abonos.saldo_posterior',
                'abonos.id_usuario',
                DB::raw('users.name as usuario_nombre'),
                'abonos.es_devolucion',
                'abonos.id_credito',
                DB::raw("'abono' as tipo_registro"),
                DB::raw("GROUP_CONCAT(DISTINCT conceptos_abono.tipo_concepto SEPARATOR ', ') as tipos_concepto")
            ])
            ->join('conceptos', 'abonos.id_concepto', '=', 'conceptos.id')
            ->leftJoin('conceptos_abono', 'abonos.id_abono', '=', 'conceptos_abono.id_abono')
            ->leftJoin('users', 'abonos.id_usuario', '=', 'users.id')
            ->where('abonos.id_credito', $creditoActivo->id_credito) // Solo del crédito específico
            ->groupBy([
                'abonos.id_abono',
                'abonos.fecha_pago',
                'abonos.created_at',
                'conceptos.nombre',
                'abonos.monto_abono',
                'abonos.saldo_posterior',
                'abonos.id_usuario',
                DB::raw('users.name'),
                'abonos.es_devolucion',
                'abonos.id_credito'
            ])
            ->union(
                DB::table('creditos')
                    ->leftJoin('users', 'creditos.id_usuario_creador', '=', 'users.id')
                    ->select([
                        'creditos.id_credito as id_abono',
                        'creditos.fecha_credito as fecha_pago',
                        'creditos.fecha_credito as created_at',
                        DB::raw("'Desembolso' as concepto_nombre"),
                        DB::raw("(creditos.valor_credito * (1 + creditos.porcentaje_interes/100)) as monto_abono"),
                        DB::raw("(creditos.valor_credito * (1 + creditos.porcentaje_interes/100)) as saldo_posterior"),
                        DB::raw('creditos.id_usuario_creador as id_usuario'),
                        DB::raw('users.name as usuario_nombre'),
                        DB::raw("false as es_devolucion"),
                        'creditos.id_credito',
                        DB::raw("'credito' as tipo_registro"),
                        DB::raw("NULL as tipos_concepto")
                    ])
                    ->where('creditos.id_credito', $creditoActivo->id_credito) // Solo del crédito específico
            )
            ->orderByRaw("CASE WHEN tipo_registro = 'credito' THEN 0 ELSE 1 END")
            ->orderBy('fecha_pago', 'asc') // Cronológico: desembolso primero, luego abonos
            ->orderBy('created_at', 'asc');

        return $query;
    }

    private function calcularSaldoHasta($recordActual)
    {
        $credito = $this->getCreditoActivo();
        if (!$credito) {
            return 0;
        }

        if ($credito->es_adicional) {
            $saldoBase = (float) $credito->saldo_actual;
            if ($recordActual->tipo_registro === 'credito') {
                return (float) $credito->valor_credito;
            }
            $abonosDespues = Abonos::where('id_credito', $credito->id_credito)
                ->where('es_devolucion', false)
                ->where(function($query) use ($recordActual) {
                    $query->where('fecha_pago', '>', $recordActual->fecha_pago)
                          ->orWhere(function($subQuery) use ($recordActual) {
                              $subQuery->where('fecha_pago', '=', $recordActual->fecha_pago)
                                       ->where('id_abono', '>', $recordActual->id_abono);
                          });
                })
                ->sum('monto_abono');
            return $saldoBase + $abonosDespues;
        } else {
            $montoTotalConIntereses = (float) $credito->valor_credito * (1 + ((float) $credito->porcentaje_interes / 100));
            $descuentoAplicado = (float) ($credito->descuento_aplicado ?? 0);
            if ($recordActual->tipo_registro === 'credito') {
                return $montoTotalConIntereses - $descuentoAplicado;
            }
            $abonosHasta = Abonos::where('id_credito', $credito->id_credito)
                ->where('es_devolucion', false)
                ->where(function($query) use ($recordActual) {
                    $query->where('fecha_pago', '<', $recordActual->fecha_pago)
                          ->orWhere(function($subQuery) use ($recordActual) {
                              $subQuery->where('fecha_pago', '=', $recordActual->fecha_pago)
                                       ->where('id_abono', '<=', $recordActual->id_abono);
                          });
                })
                ->sum('monto_abono');
            return $montoTotalConIntereses - $descuentoAplicado - $abonosHasta;
        }
    }


}
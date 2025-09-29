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

    public function mount(): void
    {
        // Obtener el record desde la URL si no está inicializado
        if (!isset($this->record)) {
            $clienteId = request()->route('cliente');
            if ($clienteId) {
                $this->record = Clientes::findOrFail($clienteId);
            }
        }
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

        // Si no hay ningún crédito, retornar query vacío
        if (!$creditoActivo) {
            return Abonos::query()->whereRaw('1 = 0'); // Query que no retorna resultados
        }

        // Obtener solo los registros del crédito activo/último crédito
        $query = Abonos::query()
            ->select([
                'abonos.id_abono',
                'abonos.fecha_pago',
                'abonos.created_at',
                'conceptos.nombre as concepto_nombre',
                'abonos.monto_abono',
                'abonos.saldo_posterior',
                'abonos.id_usuario',
                'abonos.es_devolucion',
                'abonos.id_credito',
                DB::raw("'abono' as tipo_registro"),
                DB::raw("GROUP_CONCAT(DISTINCT conceptos_abono.tipo_concepto SEPARATOR ', ') as tipos_concepto")
            ])
            ->join('conceptos', 'abonos.id_concepto', '=', 'conceptos.id')
            ->leftJoin('conceptos_abono', 'abonos.id_abono', '=', 'conceptos_abono.id_abono')
            ->where('abonos.id_credito', $creditoActivo->id_credito)
            ->groupBy([
                'abonos.id_abono',
                'abonos.fecha_pago',
                'abonos.created_at',
                'conceptos.nombre',
                'abonos.monto_abono',
                'abonos.saldo_posterior',
                'abonos.id_usuario',
                'abonos.es_devolucion',
                'abonos.id_credito'
            ])
            ->union(
                DB::table('creditos')
                    ->select([
                        'creditos.id_credito as id_abono',
                        'creditos.fecha_credito as fecha_pago',
                        'creditos.fecha_credito as created_at',
                        DB::raw("'Desembolso' as concepto_nombre"),
                        DB::raw("(creditos.valor_credito * (1 + creditos.porcentaje_interes/100)) as monto_abono"),
                        DB::raw("(creditos.valor_credito * (1 + creditos.porcentaje_interes/100)) as saldo_posterior"),
                        DB::raw("NULL as id_usuario"),
                        DB::raw("false as es_devolucion"),
                        'creditos.id_credito',
                        DB::raw("'credito' as tipo_registro"),
                        DB::raw("NULL as tipos_concepto")
                    ])
                    ->where('creditos.id_credito', $creditoActivo->id_credito)
            )
            ->orderByRaw("CASE WHEN tipo_registro = 'credito' THEN 0 ELSE 1 END")
            ->orderBy('fecha_pago', 'asc')
            ->orderBy('created_at', 'asc');

        return $query;
    }


}
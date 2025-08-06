<?php

namespace App\Filament\Widgets;

use App\Models\Clientes;
use App\Models\ConceptoAbono;
use App\Models\ConceptoCredito;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Session;

class YapeClientesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Clientes::with([
            'creditos' => function ($q) {
                $q->where('saldo_actual', '>', 0)
                ->whereHas('conceptosCredito', function ($cc) {
                    $cc->where('tipo_concepto', 'Yape');
                });
            },
            'yapeCliente'
        ])
        ->whereHas('creditos', function ($q) {
            $q->where('saldo_actual', '>', 0)
            ->whereHas('conceptosCredito', function ($cc) {
                $cc->where('tipo_concepto', 'Yape');
            });
        })
        ->where(function ($q) {
            $q->whereRaw("
                (
                    SELECT COALESCE(SUM(cc.monto), 0)
                    FROM conceptos_credito cc
                    INNER JOIN creditos c ON cc.id_credito = c.id_credito
                    WHERE c.id_cliente = clientes.id_cliente
                    AND c.saldo_actual > 0
                    AND cc.tipo_concepto = 'Yape'
                ) != (
                    SELECT COALESCE(SUM(ca.monto), 0)
                    FROM conceptos_abono ca
                    INNER JOIN abonos a ON ca.id_abono = a.id_abono
                    INNER JOIN creditos c2 ON a.id_credito = c2.id_credito
                    WHERE c2.id_cliente = clientes.id_cliente
                    AND c2.saldo_actual > 0
                    AND ca.tipo_concepto = 'Yape'
                )
            ");
        });

        // Filtrar por ruta desde la sesión
        $rutaId = Session::get('selected_ruta_id');
        if ($rutaId) {
            $query->where('id_ruta', $rutaId);
        }

        return $query;
    }



    protected function getTableColumns(): array
    {
        return [
            // Nombre del cliente
            TextColumn::make('nombre_completo')
                ->label('Cliente')
                ->searchable(),

            // Nombre Yape (si existe, sino nombre del cliente)
            TextColumn::make('nombre_yape')
                ->label('Nombre Yape')
                ->getStateUsing(function (Clientes $record) {
                    return $record->yapeCliente->first()->nombre ?? $record->nombre_completo;
                })
                ->searchable(),

            // Total Crédito (solo de créditos activos)
            TextColumn::make('total_credito')
                ->label('Total Crédito')
                ->getStateUsing(function (Clientes $record) {
                    return $record->creditos->sum(function($credito) {
                        // Si es adicional, mostrar solo el saldo actual
                        if ($credito->es_adicional) {
                            return $credito->saldo_actual;
                        }
                        // Para créditos normales, calcular capital + intereses
                        return $credito->valor_credito + ($credito->valor_credito * ($credito->porcentaje_interes / 100));
                    });
                })
                ->money('PEN', true),

            // Yapear
            TextColumn::make('yapear')
                ->label('Yapear')
                ->getStateUsing(function (Clientes $record) {
                    return ConceptoCredito::whereHas('credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0); // Solo créditos activos
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');
                })
                ->money('PEN', true),

            // Yapeado
            TextColumn::make('yapeado')
                ->label('Yapeado')
                ->getStateUsing(function (Clientes $record) {
                    return ConceptoAbono::whereHas('abono.credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0); // Solo créditos activos
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');
                })
                ->money('PEN', true),

            // Faltante
            TextColumn::make('faltante')
                ->label('Faltante')
                ->getStateUsing(function (Clientes $record) {
                    $yapear = ConceptoCredito::whereHas('credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    $yapeado = ConceptoAbono::whereHas('abono.credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    return max(0, $yapear - $yapeado);
                })
                ->money('PEN', true),

            // Devolución
            TextColumn::make('devolucion')
                ->label('Devolución')
                ->getStateUsing(function (Clientes $record) {
                    $yapear = ConceptoCredito::whereHas('credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    $yapeado = ConceptoAbono::whereHas('abono.credito', function ($q) use ($record) {
                            $q->where('id_cliente', $record->id_cliente)
                              ->where('saldo_actual', '>', 0);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    return ($yapeado > $yapear) ? ($yapeado - $yapear) : 0;
                })
                ->money('PEN', true)
                ->color('success'),
        ];
    }
}
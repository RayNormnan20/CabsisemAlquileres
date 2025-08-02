<?php

namespace App\Filament\Widgets;

use App\Models\ConceptoAbono;
use App\Models\ConceptoCredito;
use App\Models\YapeCliente;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Session;

class YapeClientesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = YapeCliente::with(['cliente', 'cliente.creditos']);

        // Filtrar por ruta desde la sesión
        $rutaId = Session::get('selected_ruta_id');
        if ($rutaId) {
            $query->whereHas('cliente', function($q) use ($rutaId) {
                $q->where('id_ruta', $rutaId);
            });
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('cliente.nombre_completo')
                ->label('Cliente')
                ->searchable(),

            TextColumn::make('nombre')
                ->label('Nombre Yape')
                ->searchable(),

            TextColumn::make('Total Crédito')
                ->getStateUsing(function (YapeCliente $record) {
                    if (!$record->cliente) {
                        return 0;
                    }
                    $totalCapital = $record->cliente->creditos->sum('valor_credito');
                    $totalIntereses = $record->cliente->creditos->sum(function ($credito) {
                        return $credito->valor_credito * ($credito->porcentaje_interes / 100);
                    });
                    $totalConIntereses = $totalCapital + $totalIntereses;
                    return $totalConIntereses ?: 0;
                })
                ->money('PEN', true),

            TextColumn::make('Yapear')
                ->getStateUsing(function (YapeCliente $record) {
                    return ConceptoCredito::whereHas('credito', function ($query) use ($record) {
                            $query->where('id_cliente', $record->id_cliente);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');
                })
                ->money('PEN', true),

            TextColumn::make('Yapeado')
                ->getStateUsing(function (YapeCliente $record) {
                    return ConceptoAbono::whereHas('abono', function ($query) use ($record) {
                            $query->where('id_cliente', $record->id_cliente);
                        })
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');
                })
                ->money('PEN', true),

            TextColumn::make('Faltante')
                ->getStateUsing(function (YapeCliente $record) {
                    $yapear = ConceptoCredito::whereHas('credito', fn($q) => $q->where('id_cliente', $record->id_cliente))
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    $yapeado = ConceptoAbono::whereHas('abono', fn($q) => $q->where('id_cliente', $record->id_cliente))
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    return max(0, $yapear - $yapeado);
                })
                ->money('PEN', true),

            TextColumn::make('Devolución')
                ->getStateUsing(function (YapeCliente $record) {
                    $yapear = ConceptoCredito::whereHas('credito', fn($q) => $q->where('id_cliente', $record->id_cliente))
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    $yapeado = ConceptoAbono::whereHas('abono', fn($q) => $q->where('id_cliente', $record->id_cliente))
                        ->where('tipo_concepto', 'Yape')
                        ->sum('monto');

                    return ($yapeado > $yapear) ? ($yapeado - $yapear) : 0;
                })
                ->money('PEN', true)
                ->color('success'),
        ];
    }
}

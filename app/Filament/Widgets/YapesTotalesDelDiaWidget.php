<?php

namespace App\Filament\Widgets;

use App\Models\ConceptoAbono;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

class YapesTotalesDelDiaWidget extends BaseWidget
{
protected int|string|array $columnSpan = 1;


    protected function getTableQuery(): Builder
    {
        return ConceptoAbono::query()
            ->join('abonos', 'conceptos_abono.id_abono', '=', 'abonos.id_abono')
            ->join('users', 'abonos.id_usuario', '=', 'users.id')
            ->where('conceptos_abono.tipo_concepto', 'Yape')
            ->whereDate('abonos.created_at', Carbon::today())
            ->select([
                'conceptos_abono.id_concepto_abono',
                'conceptos_abono.monto',
                'users.name as usuario_nombre',
                'abonos.created_at',
                'abonos.id_usuario',
                DB::raw('CONCAT(conceptos_abono.id_concepto_abono, "_", abonos.id_usuario) as id')
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('usuario_nombre')
                ->label('Usuario')
                ->searchable(),

            TextColumn::make('monto')
                ->label('Monto Yape')
                ->money('PEN', true)
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i'),

            TextColumn::make('total_usuario')
                ->label('Total Yapes')
                ->money('PEN', true)
                ->getStateUsing(function ($record) {
                    // Obtener la suma de todos los yapes del usuario en el día
                    return ConceptoAbono::query()
                        ->join('abonos', 'conceptos_abono.id_abono', '=', 'abonos.id_abono')
                        ->where('conceptos_abono.tipo_concepto', 'Yape')
                        ->where('abonos.id_usuario', $record->id_usuario)
                        ->whereDate('abonos.created_at', Carbon::today())
                        ->sum('conceptos_abono.monto');
                }),
        ];
    }

    protected function getTableFooter(): ?array
    {
        return [
            Tables\Columns\TextColumn::make('usuario_nombre')
                ->label('Total General'),

            Tables\Columns\TextColumn::make('total_general')
                ->money('PEN', true)
                ->getStateUsing(function () {
                    return ConceptoAbono::query()
                        ->join('abonos', 'conceptos_abono.id_abono', '=', 'abonos.id_abono')
                        ->where('conceptos_abono.tipo_concepto', 'Yape')
                        ->whereDate('abonos.created_at', Carbon::today())
                        ->sum('conceptos_abono.monto');
                }),
        ];
    }
}

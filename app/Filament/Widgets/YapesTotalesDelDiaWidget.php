<?php

namespace App\Filament\Widgets;

use App\Models\ConceptoAbono;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class YapesTotalesDelDiaWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('Listar Yapes Totales Del Dias');
    }


    protected function getTableQuery(): Builder
    {
        return ConceptoAbono::query()
            ->join('abonos', 'conceptos_abono.id_abono', '=', 'abonos.id_abono')
            ->join('users', 'abonos.id_usuario', '=', 'users.id')
            ->where('conceptos_abono.tipo_concepto', 'Yape')
            ->whereDate('abonos.created_at', Carbon::today())
            ->select([
                DB::raw('CONCAT("usuario_", abonos.id_usuario) as id'), // id único
                'abonos.id_usuario',
                'users.name as usuario_nombre',
                DB::raw('SUM(conceptos_abono.monto) as total_yapes'),
                DB::raw('MAX(abonos.created_at) as ultima_fecha')
            ])
            ->groupBy('abonos.id_usuario', 'users.name');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('usuario_nombre')
                ->label('Usuario')
                ->searchable(),

            TextColumn::make('total_yapes')
                ->label('Monto Total Yapes')
                ->money('PEN', true)
                ->sortable(),
            /*
            TextColumn::make('ultima_fecha')
                ->label('Última Fecha')
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
                */
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

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }
}
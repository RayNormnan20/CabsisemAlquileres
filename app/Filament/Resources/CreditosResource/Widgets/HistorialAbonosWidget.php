<?php

namespace App\Filament\Resources\CreditosResource\Widgets;

use App\Models\Abonos;
use App\Models\Creditos;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HistorialAbonosWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    public Creditos $record;

    public function getTableQuery(): Builder
    {
        return Abonos::query()
            ->select([
                'abonos.id_abono',
                'abonos.fecha_pago',
                'abonos.created_at',
                'conceptos.nombre as concepto_nombre',
                'abonos.monto_abono',
                'abonos.saldo_posterior',
                'abonos.id_usuario',
                DB::raw("'abono' as tipo_registro")
            ])
            ->join('conceptos', 'abonos.id_concepto', '=', 'conceptos.id')
            ->where('abonos.id_credito', $this->record->id_credito)
            ->union(
                DB::table('creditos')
                    ->select([
                        'creditos.id_credito as id_abono',
                        'creditos.fecha_credito as fecha_pago',
                        'creditos.fecha_credito as created_at',
                        DB::raw("'Desembolso' as concepto_nombre"),
                        'creditos.valor_credito as monto_abono',
                        DB::raw("(creditos.valor_credito * (1 + creditos.porcentaje_interes/100)) as saldo_posterior"),
                        DB::raw("NULL as id_usuario"),
                        DB::raw("'credito' as tipo_registro")
                    ])
                    ->where('creditos.id_credito', $this->record->id_credito)
            )
            ->orderBy('fecha_pago', 'asc');
    }

    public function isTableSearchable(): bool
    {
        return false;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('fecha_pago')
                ->label('Hora')
                ->time('H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('concepto_nombre')
                ->label('Concepto')
                ->sortable(),

            Tables\Columns\TextColumn::make('monto_abono')
                ->label('Cantidad')
                ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                ->sortable(),

            Tables\Columns\TextColumn::make('saldo_posterior')
                ->label('Saldo')
                ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                ->sortable()
                ->color(fn ($record) => $record->saldo_posterior < 0 ? 'danger' : null),

            Tables\Columns\TextColumn::make('metodos_pago')
                ->label('Métodos')
                ->formatStateUsing(function ($record) {
                    if ($record->tipo_registro === 'credito') {
                        return '---';
                    }

                    $abono = Abonos::find($record->id_abono);
                    return $abono
                        ? $abono->conceptosabonos
                            ->map(fn($c) => $c->tipo_concepto . ': S/ ' . number_format($c->monto, 2))
                            ->implode(' | ')
                        : '';
                }),

        ];
    }

    protected function getTableFilters(): array
    {
        return [];
    }

    protected function getTableRecordClassesUsing(): ?\Closure
    {
        return function ($record) {
            return $record->tipo_registro === 'credito' ? 'bg-gray-100 font-semibold' : null;
        };
    }


   public function render(): \Illuminate\Contracts\View\View
    {
        $abonos = Abonos::where('id_credito', $this->record->id_credito);

        $totalAbonos = $abonos->sum('monto_abono');
        $ultimoSaldoPosterior = $this->record->saldo_actual;

        return view('filament.resources.creditos-resource.widgets.historial-abonos', [
            'totalCantidad' => $totalAbonos,
            'ultimoSaldoPosterior' => $ultimoSaldoPosterior,
        ]);
    }
 /*
    protected function getTableActions(): array
    {
        return [


            Tables\Actions\DeleteAction::make()
                ->label('')
                ->icon('heroicon-o-trash')
                ->tooltip('Eliminar Abono'),

        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
          //  Tables\Actions\DeleteBulkAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }
    */

}
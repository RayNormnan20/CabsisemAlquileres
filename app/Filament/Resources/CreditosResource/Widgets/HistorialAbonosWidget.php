<?php

namespace App\Filament\Resources\CreditosResource\Widgets;

use App\Models\Abonos;
use App\Models\Creditos;
use App\Models\LogActividad;
use App\Filament\Resources\AbonosResource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class HistorialAbonosWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    public Creditos $record;

    public function getTableQuery(): Builder
    {
        // Obtener todos los registros ordenados por fecha
        $query = Abonos::query()
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
            ->orderBy('fecha_pago', 'asc')
            ->orderBy('created_at', 'asc');

        return $query;
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
                ->sortable()
                ->color(function ($record) {
                    if ($record->tipo_registro === 'abono') {
                        $abono = Abonos::find($record->id_abono);
                        return $abono && $abono->es_devolucion ? 'danger' : null;
                    }
                    return null;
                }),

            Tables\Columns\BadgeColumn::make('tipo_abono')
                ->label('Tipo')
                ->formatStateUsing(function ($record) {
                    if ($record->tipo_registro === 'credito') {
                        return 'Crédito';
                    }
                    $abono = Abonos::find($record->id_abono);
                    return $abono && $abono->es_devolucion ? 'Devolución' : 'Abono';
                })
                ->colors([
                    'primary' => fn ($state) => $state === 'Crédito',
                    'success' => fn ($state) => $state === 'Abono',
                    'danger' => fn ($state) => $state === 'Devolución',
                ]),

            Tables\Columns\TextColumn::make('saldo_calculado')
                ->label('Saldo')
                ->formatStateUsing(function ($record) {
                    // Calcular el saldo dinámicamente
                    $saldo = $this->calcularSaldoHasta($record);
                    return 'S/ ' . number_format($saldo, 2);
                })
                ->sortable()
                ->color(function ($record) {
                    $saldo = $this->calcularSaldoHasta($record);
                    return $saldo < 0 ? 'danger' : null;
                }),

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

    private function calcularSaldoHasta($recordActual)
    {
        if ($this->record->es_adicional) {
            // Para créditos adicionales, usar el saldo actual del crédito como base
            // porque incluye las cuotas diarias acumuladas
            $saldoBase = $this->record->saldo_actual;
            
            // Si es el registro del crédito (desembolso), devolver el valor original
            if ($recordActual->tipo_registro === 'credito') {
                return $this->record->valor_credito;
            }
            
            // Para abonos, calcular cuánto se había abonado después de este registro
            $abonosDespues = Abonos::where('id_credito', $this->record->id_credito)
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
            // Para créditos normales, usar el cálculo tradicional
            $montoTotalConIntereses = $this->record->valor_credito * (1 + $this->record->porcentaje_interes / 100);

            // Si es el registro del crédito (desembolso), devolver el monto total
            if ($recordActual->tipo_registro === 'credito') {
                return $montoTotalConIntereses;
            }

            // Obtener todos los abonos hasta la fecha del registro actual (inclusive)
            $abonosHasta = Abonos::where('id_credito', $this->record->id_credito)
                ->where(function($query) use ($recordActual) {
                    $query->where('fecha_pago', '<', $recordActual->fecha_pago)
                          ->orWhere(function($subQuery) use ($recordActual) {
                              $subQuery->where('fecha_pago', '=', $recordActual->fecha_pago)
                                       ->where('id_abono', '<=', $recordActual->id_abono);
                          });
                })
                ->sum('monto_abono');

            return $montoTotalConIntereses - $abonosHasta;
        }
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
        $totalAbonos = Abonos::where('id_credito', $this->record->id_credito)
            ->sum('monto_abono');

        // Calcular el saldo actual correctamente según el tipo de crédito
        if ($this->record->es_adicional) {
            // Para créditos adicionales, usar directamente el saldo_actual del crédito
            // que ya incluye las cuotas diarias acumuladas
            $saldoActual = $this->record->saldo_actual;
        } else {
            // Para créditos normales, calcular como siempre
            $montoTotalConIntereses = $this->record->valor_credito * (1 + $this->record->porcentaje_interes / 100);
            $saldoActual = $montoTotalConIntereses - $totalAbonos;
        }

        return view('filament.resources.creditos-resource.widgets.historial-abonos', [
            'totalCantidad' => $totalAbonos,
            'ultimoSaldoPosterior' => $saldoActual,
        ]);
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('edit')
                ->label('')
                ->icon('heroicon-o-pencil-alt')
                ->color('primary')
                ->tooltip('Editar Abono')
                ->url(function ($record) {
                    if ($record->tipo_registro === 'abono') {
                        session([
                            'return_to_credito_view' => true,
                            'credito_id_return' => $this->record->id_credito
                        ]);

                        return AbonosResource::getUrl('edit', ['record' => $record->id_abono]);
                    }
                    return null;
                })
                ->visible(fn ($record) => $record->tipo_registro === 'abono'),

            Tables\Actions\Action::make('delete')
                ->label('')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->button()
                ->requiresConfirmation()
                ->modalHeading('Eliminar Abono')
                ->modalSubheading('¿Está seguro que desea eliminar este abono? Esta acción no se puede deshacer.')
                ->modalButton('Sí, eliminar')
                ->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        // Obtener el abono completo
                        $abono = Abonos::findOrFail($record->id_abono);

                        // Obtener datos para el log antes de eliminar
                        $clienteNombre = $abono->cliente?->nombre . ' ' . $abono->cliente?->apellido;
                        $rutaNombre = $abono->ruta?->nombre ?? 'Ruta desconocida';

                        $credito = $abono->credito()->lockForUpdate()->first();

                        if (! $credito) {
                            throw new \Exception('Crédito asociado no encontrado.');
                        }
                        
                        if ($credito->es_adicional) {
                            // Para créditos adicionales, recalcular el saldo correctamente
                            $diasTranscurridos = now()->diffInDays($credito->fecha_credito);
                            $cuotasDiariasAcumuladas = $diasTranscurridos * $credito->porcentaje_interes;
                            
                            // Calcular total de abonos restantes (excluyendo el que se va a eliminar)
                            $totalAbonosRestantes = Abonos::where('id_credito', $credito->id_credito)
                                ->where('id_abono', '!=', $abono->id_abono)
                                ->sum('monto_abono');
                            
                            $credito->saldo_actual = $credito->valor_credito + $cuotasDiariasAcumuladas - $totalAbonosRestantes;
                        } else {
                            // Para créditos normales, simplemente sumar el monto del abono
                            $credito->saldo_actual += $abono->monto_abono;
                        }
                        
                        $credito->save();

                        // Registrar log de actividad antes de eliminar
                        LogActividad::registrar(
                            'Abonos',
                            "Eliminó un abono de la ruta {$rutaNombre} para el cliente {$clienteNombre} del día " . $abono->fecha_pago->format('d M Y') . " por S/" . number_format($abono->monto_abono, 2),
                            [
                                'abono_id' => $abono->id_abono,
                                'cliente_id' => $abono->id_cliente,
                                'ruta_id' => $abono->id_ruta,
                                'fecha_pago' => $abono->fecha_pago->format('Y-m-d'),
                                'monto_abono' => $abono->monto_abono
                            ]
                        );

                        $abono->delete();
                    });

                    Notification::make()
                        ->title('Abono eliminado')
                        ->body('El abono ha sido eliminado correctamente.')
                        ->success()
                        ->send();
                })
                ->extraAttributes([
                    'title' => 'Eliminar',
                    'class' => 'hover:bg-danger-50 rounded-full'
                ])
                ->visible(fn ($record) => $record->tipo_registro === 'abono'),
        ];
    }





}
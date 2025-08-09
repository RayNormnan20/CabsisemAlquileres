<?php

namespace App\Filament\Resources;

use App\Models\PlanillaRecaudador;
use App\Models\Creditos;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use App\Filament\Resources\PlanillaRecaudadorResource\Pages;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PlanillaRecaudadorResource extends Resource
{
    protected static ?string $model = PlanillaRecaudador::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'planilla/recaudador';
    protected static ?string $navigationLabel = 'Planilla Recaudador';
    protected static ?string $modelLabel = 'Reporte';
    protected static ?string $pluralModelLabel = 'Planilla Recaudador';
    protected static ?string $navigationGroup = 'Reportes';

    protected static function getDiasAtraso($record)
    {
        if (empty($record->fecha_vencimiento)) {
            return 0;
        }

        try {
            $vencimiento = Carbon::parse($record->fecha_vencimiento);
            $hoy = Carbon::now();
            return $hoy->greaterThan($vencimiento) ? $hoy->diffInDays($vencimiento) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_credito')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ultima_fecha_pago')
                    ->label('U. Abono')
                    ->date('d/m/Y'),

                TextColumn::make('cliente_completo')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('valor_credito')
                    ->label('Crédito')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('total_abonos')
                    ->label('Abonos')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->color('success')
                    ->sortable(),

                TextColumn::make('saldo_actual')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->color(fn ($record) => $record->saldo_actual > 0 ? 'danger' : 'danger')
                    ->sortable(),

                TextColumn::make('valor_cuota')
                    ->label('Cuota')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('dias_atraso')
                    ->label('Atraso (días)')
                    ->getStateUsing(function ($record) {
                        return self::getDiasAtraso($record);
                    })
                    ->color(function ($record) {
                        $dias = self::getDiasAtraso($record);
                        return $dias > 0 ? 'danger' : 'success';
                    }),
                    /*
                BadgeColumn::make('estado_renovacion')
                    ->label('Estado')
                    ->getStateUsing(function ($record) {
                        $credito = Creditos::find($record->id_credito);
                        $diasAtraso = self::getDiasAtraso($record);
                        
                        if ($record->saldo_actual <= 0) {
                            return 'Pagado';
                        } elseif ($credito && $credito->por_renovar) {
                            return 'Por Renovar';
                        } elseif ($diasAtraso > 0) {
                            return 'Vencido';
                        } else {
                            return 'Activo';
                        }
                    })
                    ->colors([
                        'gray' => 'Pagado',
                        'warning' => 'Por Renovar',
                        'danger' => 'Vencido',
                        'success' => 'Activo',
                    ]),
                    */
            ])
            ->actions([
                Action::make('habilitar_renovacion')
                    ->label('Habilitar Renovación')
                    ->icon('heroicon-o-refresh')
                    ->color('warning')
                    ->visible(function ($record) {
                        $credito = Creditos::find($record->id_credito);
                        $diasAtraso = self::getDiasAtraso($record);
                        
                        // Solo mostrar si el crédito está vencido, tiene saldo y no está marcado para renovar
                        return $record->saldo_actual > 0 && 
                               $diasAtraso > 0 && 
                               $credito && 
                               !$credito->por_renovar;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Habilitación para Renovación')
                    ->modalSubheading(function ($record) {
                        $diasAtraso = self::getDiasAtraso($record);
                        return "¿Está seguro que desea habilitar este crédito para renovación?\n\nCliente: {$record->cliente_completo}\nDías vencidos: {$diasAtraso}\nSaldo: S/ " . number_format($record->saldo_actual, 2);
                    })
                    ->action(function ($record) {
                        $credito = Creditos::find($record->id_credito);
                        
                        if ($credito) {
                            $credito->por_renovar = true;
                            $credito->save();
                            
                            Notification::make()
                                ->title('Crédito habilitado para renovación')
                                ->warning()
                                ->send();
                        }
                    })
            ])
            ->filters([
                SelectFilter::make('ruta')
                    ->options(
                        PlanillaRecaudador::query()
                            ->pluck('ruta', 'ruta')
                            ->unique()
                            ->sort()
                    )
                    ->label('Ruta'),

                Filter::make('atrasados')
                    ->label('Solo atrasados')
                    ->query(function (Builder $query) {
                        return $query->whereDate('fecha_vencimiento', '<', Carbon::now());
                    })
                    ->default(false),
            ])
            ->defaultSort('fecha_vencimiento', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanillaRecaudadors::route('/'),
        ];
    }
}
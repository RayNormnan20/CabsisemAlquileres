<?php

namespace App\Filament\Resources;

use App\Models\Movimiento;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\VistaMovimientoResource\Pages;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VistaMovimientoResource extends Resource
{
    protected static ?string $model = Movimiento::class;
    protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'movimientos/ingresos-gastos';
    protected static ?string $navigationLabel = 'Ingresos y Gastos';
    protected static ?string $modelLabel = 'Movimiento';
    protected static ?string $pluralModelLabel = 'Ingresos y Gastos';
    protected static ?string $navigationGroup = 'Movimientos';

   public static function table(Table $table): Table
{
    $saldoAcumulado = 0;

    return $table
        ->columns([
            TextColumn::make('fecha')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i')
                ->sortable(),

            TextColumn::make('usuario')
                ->label('Usuario'),

            TextColumn::make('concepto')
                ->label('Concepto')
                ->searchable(),

            TextColumn::make('monto')
                ->label('Cantidad')
                ->formatStateUsing(function ($state, $record) {
                    $tipo = strtolower($record->tipo_concepto);
                    $formatted = number_format($state, 2);
                    return $tipo === 'gastos' ? "-S/ $formatted" : "S/ $formatted";
                })
                ->color(fn ($record) => strtolower($record->tipo_concepto) === 'gastos' ? 'danger' : 'gray')
                ->sortable(),

            TextColumn::make('saldo_acumulado')
                ->label('Saldo')
                ->getStateUsing(function ($record) use (&$saldoAcumulado) {
                    $tipo = strtolower($record->tipo_concepto);
                    if ($tipo === 'ingresos') {
                        $saldoAcumulado += $record->monto;
                    } elseif ($tipo === 'gastos') {
                        $saldoAcumulado -= $record->monto;
                    }
                    return 'S/ ' . number_format($saldoAcumulado, 2);
                }),

            BadgeColumn::make('tipo_concepto')
                ->label('Tipo')
                ->colors([
                    'success' => 'Ingresos',
                    'danger' => 'Gastos',
                ]),
                
        ])
        ->defaultSort('fecha', 'asc');
        
}
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVistaMovimientos::route('/'),
        ];
    }



    public static function form(\Filament\Resources\Form $form): \Filament\Resources\Form
    {
        return $form->schema([]);
    }


    public static function getRelations(): array
    {
        return [];
    }
}
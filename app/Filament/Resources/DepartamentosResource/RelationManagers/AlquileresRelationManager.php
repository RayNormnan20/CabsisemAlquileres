<?php

namespace App\Filament\Resources\DepartamentosResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class AlquileresRelationManager extends RelationManager
{
    protected static string $relationship = 'alquileres';
    protected static ?string $recordTitleAttribute = 'id_alquiler';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inquilino.nombre_completo')
                    ->label('Inquilino')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '—'),

                TextColumn::make('precio_mensual')
                    ->label('Precio')
                    ->money('PEN', true)
                    ->sortable(),

                BadgeColumn::make('estado_alquiler')
                    ->label('Estado')
                    ->enum([
                        'activo' => 'Activo',
                        'finalizado' => 'Finalizado',
                        'suspendido' => 'Suspendido',
                    ])
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'finalizado',
                        'warning' => 'suspendido',
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
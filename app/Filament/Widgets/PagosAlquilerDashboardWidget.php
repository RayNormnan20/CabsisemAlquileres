<?php

namespace App\Filament\Widgets;

use App\Models\PagoAlquiler;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class PagosAlquilerDashboardWidget extends BaseWidget
{
    protected static ?string $heading = 'Pagos de alquiler recientes';

    protected function getTableQuery(): Builder
    {
        $query = PagoAlquiler::query()->with(['alquiler.departamento.edificio', 'usuarioRegistro']);

        $rutaId = Session::get('selected_ruta_id');

        if ($rutaId) {
            $query->where('id_ruta', $rutaId);
        }

        return $query->latest('fecha_pago');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('fecha_pago')
                ->label('Fecha')
                ->date('d-m-Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('alquiler.departamento.edificio.nombre')
                ->label('Edificio')
                ->limit(20),
            Tables\Columns\TextColumn::make('alquiler.departamento.numero_departamento')
                ->label('Depto'),
            Tables\Columns\TextColumn::make('alquiler.inquilino.nombre')
                ->label('Inquilino')
                ->limit(20),
            Tables\Columns\TextColumn::make('monto_pagado')
                ->label('Monto')
                ->money('PEN', true)
                ->sortable(),
            Tables\Columns\TextColumn::make('usuarioRegistro.name')
                ->label('Registrado por')
                ->limit(15),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}


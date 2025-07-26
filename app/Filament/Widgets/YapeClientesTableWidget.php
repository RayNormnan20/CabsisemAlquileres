<?php

namespace App\Filament\Widgets;

use App\Models\YapeCliente;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class YapeClientesTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return YapeCliente::with('cliente');
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
            TextColumn::make('monto')
                ->label('Monto')
                ->searchable(),
            TextColumn::make('entregar')
                ->label('Entregar')
                ->searchable(),

            TextColumn::make('Total')
                ->label('Total')
                ->getStateUsing(fn () => '-'),

            TextColumn::make('Faltante')
                ->label('Faltante')
                ->getStateUsing(fn () => '-'),

            TextColumn::make('Devolución')
                ->label('Devolución')
                ->getStateUsing(fn () => '-'),
        ];
    }
}

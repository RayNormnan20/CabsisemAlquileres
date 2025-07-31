<?php

namespace App\Filament\Widgets;

use App\Models\Clientes;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ClientesPorRenovarWidget extends BaseWidget
{
protected int|string|array $columnSpan = 1;

    protected function getTableQuery(): Builder
    {
        return Clientes::whereHas('creditos', function ($query) {
            $query->where('por_renovar', true);
        });
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nombre_completo')
                ->label('Cliente')
                ->searchable(),


            TextColumn::make('estado_credito')
                ->label('Estado')
                ->getStateUsing(fn ($record) =>
                    optional($record->creditos()->where('por_renovar', true)->latest()->first())->estado ?? 'Renovar'
                ),
        ];
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKey();
    }
}
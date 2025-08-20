<?php

namespace App\Filament\Widgets;

use App\Models\Creditos;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class SegundoRecorridoWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;
/*
    public static function canView(): bool
    {
        // Solo mostrar si el usuario tiene el rol "Administrador"
       // return Auth::user()?->hasRole('Administrador');
    }
*/
    protected function getTableQuery(): Builder
    {
        return Creditos::query()
            ->with(['cliente', 'ruta.usuarios'])
            ->where('segundo_recorrido', true)
            ->select([
                'id_credito',
                'id_cliente',
                'id_ruta',
                'segundo_recorrido'
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('cliente.nombre_completo')
                ->label('NOMBRE')
                ->searchable()
                ->getStateUsing(function ($record) {
                    return $record->cliente ? $record->cliente->nombre_completo : 'Sin cliente';
                }),

            TextColumn::make('cliente.direccion')
                ->label('DIRECCIÓN')
                ->searchable()
                ->getStateUsing(function ($record) {
                    return $record->cliente ? $record->cliente->direccion : 'Sin dirección';
                }),

            TextColumn::make('cobrador')
                ->label('COBRADOR')
                ->searchable()
                ->getStateUsing(function ($record) {
                    if ($record->ruta && $record->ruta->usuarios->isNotEmpty()) {
                        return $record->ruta->usuarios->first()->name;
                    }
                    return 'Sin asignar';
                }),
        ];
    }

    protected function getTableHeading(): ?string
    {
        return 'SEGUNDO RECORRIDO';
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id_credito;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 10;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25, 50];
    }
}
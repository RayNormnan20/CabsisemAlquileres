<?php

namespace App\Filament\Widgets;

use App\Models\YapeCliente;
use App\Models\Abonos;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class YapeUsuariosWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('Listar Usuarios Que Abonaron A Yapes');
    }

    protected function getTableQuery(): Builder
    {
        // Consulta base para obtener abonos con información de Yape
        $query = Abonos::query()
            ->with(['yapeCliente', 'usuario'])
            ->whereHas('yapeCliente', function($query) {
                $query->whereNotNull('nombre')
                      ->where('nombre', '!=', '');
            })
            ->select([
                'id_abono',
                'id_yape_cliente',
                'id_usuario',
                'monto_abono',
                'created_at'
            ]);

        // Permitir que Filament maneje los filtros naturalmente
        // Los filtros se aplicarán automáticamente a la consulta base

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('yapeCliente.nombre')
                ->label('NOMBRE YAPE')
                ->searchable()
                ->sortable()
                ->getStateUsing(function ($record) {
                    return $record->yapeCliente ? $record->yapeCliente->nombre : 'Sin nombre';
                }),

            TextColumn::make('usuario.name')
                ->label('USUARIO QUE ABONÓ')
                ->searchable()
                ->sortable()
                ->getStateUsing(function ($record) {
                    return $record->usuario ? $record->usuario->name : 'Sin usuario';
                }),

            TextColumn::make('monto_abono')
                ->label('MONTO ABONADO')
                ->money('PEN', true)
                ->sortable(),

            TextColumn::make('created_at')
                ->label('FECHA ABONO')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('id_yape_cliente')
                ->label('Filtrar por Nombre Yape')
                ->options(function () {
                    return YapeCliente::whereNotNull('nombre')
                        ->where('nombre', '!=', '')
                        ->pluck('nombre', 'id')
                        ->unique()
                        ->sort();
                })
                ->searchable()
                ->placeholder('Seleccionar nombre Yape'),

            SelectFilter::make('id_usuario')
                ->label('Filtrar por Usuario')
                ->relationship('usuario', 'name')
                ->searchable()
                ->placeholder('Seleccionar usuario'),
        ];
    }

    protected function getTableHeading(): ?string
    {
        return 'USUARIOS QUE ABONARON A YAPE';
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id_abono;
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

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }
}
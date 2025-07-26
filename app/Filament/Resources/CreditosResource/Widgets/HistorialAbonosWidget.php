<?php

namespace App\Filament\Resources\CreditosResource\Widgets;

use App\Models\Abonos;
use App\Models\Creditos;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class HistorialAbonosWidget extends BaseWidget implements HasTable
{
    // protected static ?string $heading = 'Historial de Abonos'; // Ya lo tienes comentado, ¡bien!
    protected int|string|array $columnSpan = 'full';

    public Creditos $record;

    public function getTableQuery(): Builder
    {
        return Abonos::query()
            ->where('id_credito', $this->record->id_credito)
            ->orderBy('fecha_pago', 'asc');
    }

    // <--- AÑADE ESTE MÉTODO PARA DESHABILITAR LA BÚSQUEDA
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

            Tables\Columns\TextColumn::make('concepto.nombre')
                ->label('Concepto')
                ->sortable(), // <--- QUITA ->searchable() DE AQUÍ
                // ->searchable(), // Comenta o elimina esta línea si la tienes

            Tables\Columns\TextColumn::make('monto_abono')
                ->label('Cantidad')
                ->money('PEN', true)
                ->sortable(),

            Tables\Columns\TextColumn::make('saldo_anterior')
                ->label('Saldo')
                ->money('PEN', true)
                ->sortable(),

            Tables\Columns\TextColumn::make('saldo_posterior')
                ->label('Saldo Posterior')
                ->money('PEN', true)
                ->sortable()
                ->color(fn ($record) => $record->saldo_posterior < 0 ? 'danger' : 'success'),

            Tables\Columns\TextColumn::make('usuario.name')
                ->label('Registrado por')
                ->sortable(), // <--- QUITA ->searchable() DE AQUÍ
                // ->searchable(), // Comenta o elimina esta línea si la tienes
        ];
    }

    // <--- ASEGÚRATE DE QUE ESTE MÉTODO RETORNE UN ARRAY VACÍO
    protected function getTableFilters(): array
    {
        return [
            // No hay filtros, por lo tanto, no se mostrará el botón de filtro
        ];
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumenAlquilerResource\Pages;
use App\Models\Alquiler;
use App\Models\PagoAlquiler;
use App\Models\Edificio;
use App\Models\Departamento;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ResumenAlquilerResource extends Resource
{
    protected static ?string $model = Alquiler::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Gestión de Alquileres';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Historial de Alquileres';

    protected static ?string $slug = 'historial-alquileres';

    protected static ?string $recordTitleAttribute = 'cliente_nombre';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    protected static function getNavigationLabel(): string
    {
        return __('Historial Alquileres');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Alquileres');
    }

    public static function canCreate(): bool
    {
        return false; // Solo lectura
    }

    public static function canEdit($record): bool
    {
        return false; // Solo lectura
    }

    public static function canDelete($record): bool
    {
        return false; // Solo lectura
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mes')
                    ->label('MES')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('TOTAL')
                    ->money('PEN', true)
                    ->sortable(),

                BadgeColumn::make('estado')
                    ->label('ESTADO')
                    ->colors([
                        'success' => 'CANCELADO',
                        'warning' => 'PENDIENTE',
                        'danger' => 'DEUDA PENDIENTE'
                    ])
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumenAlquiler::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->where('estado_alquiler', 'activo');
    }
}
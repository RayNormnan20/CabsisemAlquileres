<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogActividadResource\Pages;
use App\Models\LogActividad;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Resources\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class LogActividadResource extends Resource
{
    protected static ?string $model = LogActividad::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Actividades');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Log Sistema');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->color(function ($record) {
                        return match ($record->tipo) {
                            'Rutas' => 'primary',
                            'Créditos' => 'success',
                            'Clientes' => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('mensaje')
                    ->label('Acción')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('metadata')
                    ->label('Detalles')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'Rutas' => 'Rutas',
                        'Créditos' => 'Créditos',
                        'Clientes' => 'Clientes',
                        'Usuarios' => 'Usuarios',
                    ])
                    ->label('Filtrar por Tipo'),

                SelectFilter::make('user_id')
                    ->relationship('usuario', 'name')
                    ->label('Filtrar por Usuario'),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogActividads::route('/'),
        ];
    }
}
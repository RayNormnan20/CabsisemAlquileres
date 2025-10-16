<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiasNoLaborablesResource\Pages;
use App\Models\DiaNoLaborable;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DiasNoLaborablesResource extends Resource
{
    protected static ?string $model = DiaNoLaborable::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Días No Laborables');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Créditos');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->required()
                    ->unique(table: 'dias_no_laborables', column: 'fecha')
                    ->displayFormat('d/m/Y')
                    ->placeholder('Seleccione una fecha'),

                TextInput::make('motivo')
                    ->label('Motivo')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Feriado Nacional, Día Festivo, etc.')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                BadgeColumn::make('tipo_dia')
                    ->label('Tipo')
                    ->getStateUsing(function ($record) {
                        $fecha = Carbon::parse($record->fecha);
                        if ($fecha->isSunday()) {
                            return 'Domingo';
                        } elseif (in_array($record->motivo, ['Año Nuevo', 'Día del Trabajo', 'Independencia del Perú', 'Fiesta Nacional', 'Santa Rosa de Lima', 'Combate de Angamos', 'Todos los Santos', 'Inmaculada Concepción', 'Navidad'])) {
                            return 'Feriado Nacional';
                        } else {
                            return 'Día Especial';
                        }
                    })
                    ->colors([
                        'primary' => 'Domingo',
                        'success' => 'Feriado Nacional',
                        'warning' => 'Día Especial',
                    ]),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('fecha_desde')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: ' . Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: ' . Carbon::parse($data['hasta'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('fecha', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiasNoLaborables::route('/'),
            'create' => Pages\CreateDiasNoLaborables::route('/create'),
            'edit' => Pages\EditDiasNoLaborables::route('/{record}/edit'),
        ];
    }
}
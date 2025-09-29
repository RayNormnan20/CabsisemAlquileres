<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagosAlquilerResource\Pages;
use App\Models\PagoAlquiler;
use App\Models\Alquiler;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagosAlquilerResource extends Resource
{
    protected static ?string $model = PagoAlquiler::class;
    protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?int $navigationSort = 5;

    protected static function getNavigationLabel(): string
    {
        return __('Pagos de Alquiler');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Alquileres');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Section::make('Información del Pago')
                        ->schema([
                            Select::make('id_alquiler')
                                ->label('Alquiler')
                                ->options(function () {
                                    return Alquiler::query()
                                        ->with(['departamento.edificio', 'inquilino'])
                                        ->where('estado_alquiler', 'activo')
                                        ->get()
                                        ->mapWithKeys(fn($a) => [
                                            $a->id_alquiler =>
                                            ($a->departamento?->edificio?->nombre ?? 'Sin edificio') . ' - Depto. ' .
                                            ($a->departamento?->numero_departamento ?? 'S/N') . ' (' .
                                            ($a->inquilino?->nombre_completo ?? 'Sin inquilino') . ')'
                                        ]);
                                })
                                ->required()
                                ->searchable()
                                ->preload(),

                            DatePicker::make('fecha_pago')
                                ->label('Fecha de Pago')
                                ->required()
                                ->default(now()),

                            TextInput::make('monto_pagado')
                                ->label('Monto Pagado')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required(),
                               // ->prefix('$'),

                            Select::make('metodo_pago')
                                ->label('Método de Pago')
                                ->options([
                                    'efectivo' => 'Efectivo',
                                    'Yape' => 'Yape',

                                ])
                               // ->default('Efectivo')
                                ->required(),
                        ])->columns(2),

                    Section::make('Período Correspondiente')
                        ->schema([
                            Select::make('mes_correspondiente')
                                ->label('Mes')
                                ->options([
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                                    4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                                    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                                    10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                ])
                                ->default(now()->month)
                                ->required(),

                            TextInput::make('ano_correspondiente')
                                ->label('Año')
                                ->numeric()
                                ->minValue(2020)
                                ->maxValue(2050)
                                ->default(now()->year)
                                ->required(),

                            TextInput::make('referencia_pago')
                                ->label('Referencia/Número')
                                ->maxLength(100),
                        ])->columns(3),

                    Section::make('Información Adicional')
                        ->schema([
                            Textarea::make('observaciones')
                                ->maxLength(500)
                                ->rows(3)
                                ->columnSpanFull(),

                            FileUpload::make('recibo_path')
                                ->label('Recibo/Comprobante')
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->directory('recibos')
                                ->maxSize(5120) // 5MB
                                ->columnSpanFull(),
                        ])->columns(1),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*
                TextColumn::make('id_pago_alquiler')
                    ->label('#')
                    ->sortable(),
*/
                TextColumn::make('alquiler.departamento.edificio.nombre')
                    ->label('Edificio')
                    ->searchable()
                    ->sortable()
                    ->default('Sin edificio'),

                TextColumn::make('alquiler.departamento.numero_departamento')
                    ->label('N° Dep.')
                    ->searchable()
                    ->sortable()
                    ->default('S/N'),

                TextColumn::make('alquiler.inquilino.nombre_completo')
                    ->label('Inquilino')
                    ->searchable(['alquiler.inquilino.nombre', 'alquiler.inquilino.apellido'])
                    ->sortable()
                    ->default('Sin inquilino'),

                TextColumn::make('usuarioRegistro.name')
                    ->label('Registrado por')
                    ->formatStateUsing(function ($record) {
                        if ($record->usuarioRegistro) {
                            return $record->usuarioRegistro->name ?? 'Usuario sin nombre';
                        }
                        return 'Sin usuario';
                    })
                    ->searchable(['usuarioRegistro.name'])
                    ->sortable()
                    ->default('Sin usuario'),

                TextColumn::make('fecha_pago')
                    ->label('Fecha Pago')
                    ->date()
                    ->sortable(),

                TextColumn::make('monto_pagado')
                    ->label('Monto')
                    ->money('PEN', true)
                    ->sortable(),

                TextColumn::make('periodo')
                    ->label('Período')
                    ->getStateUsing(fn($record) =>
                        date('F Y', mktime(0, 0, 0, $record->mes_correspondiente, 1, $record->ano_correspondiente))
                    )
                    ->sortable(['ano_correspondiente', 'mes_correspondiente']),

                TextColumn::make('metodo_pago')
                    ->label('Método')
                    ->enum([
                        'efectivo' => 'Efectivo',
                        'Yape' => 'Yape',

                    ])
                    ->sortable(),

                TextColumn::make('referencia_pago')
                    ->label('Referencia')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('metodo_pago')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'Yape' => 'Yape',
                    ]),

                Filter::make('fecha_pago')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '<=', $date),
                            );
                    }),

                Filter::make('mes_actual')
                    ->label('Mes Actual')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('mes_correspondiente', now()->month)
                              ->where('ano_correspondiente', now()->year)
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('primary')
                    ->size('lg')
                    ->url(fn ($record): string => static::getUrl('edit', ['record' => $record]))
                    ->extraAttributes([
                        'title' => 'Editar',
                        'class' => 'hover:bg-primary-50 rounded-full'
                    ]),
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Pago Alquiler')
                    ->modalSubheading('¿Estás seguro de que deseas eliminar este pago de alquiler? Esta acción no se puede deshacer.')
                    ->modalButton('Sí, eliminar')
                    ->successNotificationTitle('Pago eliminado')
                    ->visible(fn () => auth()->user()->can('Eliminar Pagos Alquiler')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('fecha_pago', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagosAlquilers::route('/'),
            'create' => Pages\CreatePagosAlquiler::route('/create'),
            'edit' => Pages\EditPagosAlquiler::route('/{record}/edit'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario_registro'] = Auth::id();
        return $data;
    }
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [-1 => 'Todos', 10, 25, 50, 100];
    }

    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return -1; // -1 representa "todos" en Filament
    }
}

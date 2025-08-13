<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlquileresResource\Pages;
use App\Models\Alquiler;
use App\Models\Departamento;
use App\Models\ClienteAlquiler;
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
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AlquileresResource extends Resource
{
    protected static ?string $model = Alquiler::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 4;

    protected static function getNavigationLabel(): string
    {
        return __('Alquileres');
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
                    Section::make('Información del Alquiler')
                        ->schema([
                            Select::make('id_departamento')
                                ->label('Departamento')
                                ->options(function () {
                                    return Departamento::query()
                                        ->with(['edificio', 'estado'])
                                        ->whereHas('estado', function($q) {
                                            $q->where('nombre', 'Disponible');
                                        })
                                        ->where('activo', true)
                                        ->get()
                                        ->mapWithKeys(fn($d) => [
                                            $d->id_departamento =>
                                            $d->edificio->nombre . ' - Depto. ' . $d->numero_departamento . ' (Piso ' . $d->piso . ')'
                                        ]);
                                })
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('id_cliente_alquiler')
                                ->label('Inquilino')
                                ->options(function () {
                                    return ClienteAlquiler::query()
                                        ->where('activo', true)
                                        ->orderBy('nombre')
                                        ->get()
                                        ->mapWithKeys(fn($c) => [$c->id_cliente_alquiler => $c->nombre_completo]);
                                })
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('estado_alquiler')
                                ->label('Estado del Alquiler')
                                ->options([
                                    'activo' => 'Activo',
                                    'finalizado' => 'Finalizado',
                                    'suspendido' => 'Suspendido'
                                ])
                                ->default('activo')
                                ->required(),
                        ])->columns(1),

                    Section::make('Fechas y Pagos')
                        ->schema([
                            DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->required()
                                ->default(now()),

                            DatePicker::make('fecha_fin')
                                ->label('Fecha de Fin')
                                ->after('fecha_inicio'),

                            TextInput::make('precio_mensual')
                                ->label('Precio Mensual')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required(),
                               // ->prefix('$'),

                            TextInput::make('deposito_garantia')
                                ->label('Depósito de Garantía')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0),
                               // ->prefix('$'),

                            DatePicker::make('fecha_proximo_pago')
                                ->label('Fecha Próximo Pago')
                                ->required()
                                ->default(now()->addMonth()),

                            TextInput::make('dia_pago')
                                ->label('Día de Pago del Mes')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                                ->default(1)
                                ->required(),
                        ])->columns(2),

                    Section::make('Información Adicional')
                        ->schema([
                            Textarea::make('observaciones')
                                ->maxLength(1000)
                                ->rows(3)
                                ->columnSpanFull(),

                            FileUpload::make('contrato_path')
                                ->label('Contrato (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('contratos')
                                ->maxSize(10240) // 10MB
                                ->columnSpanFull(),
                        ])->columns(1),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_alquiler')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('departamento.edificio.nombre')
                    ->label('Edificio')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('departamento.numero_departamento')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('inquilino.nombre_completo')
                    ->label('Inquilino')
                    ->searchable(['inquilino.nombre', 'inquilino.apellido'])
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),

                TextColumn::make('precio_mensual')
                    ->label('Precio')
                    ->money('PEN', true)
                    ->sortable(),

                TextColumn::make('fecha_proximo_pago')
                    ->label('Próximo Pago')
                    ->date()
                    ->sortable()
                    ->color(fn($record) => $record->fecha_proximo_pago < Carbon::now() ? 'danger' : 'success'),

                TextColumn::make('dias_atraso')
                    ->label('Días Atraso')
                    ->getStateUsing(fn($record) => $record->dias_atraso)
                    ->color(fn($record) => $record->dias_atraso > 0 ? 'danger' : 'success'),

                BadgeColumn::make('estado_alquiler')
                    ->label('Estado')
                    ->enum([
                        'activo' => 'Activo',
                        'finalizado' => 'Finalizado',
                        'suspendido' => 'Suspendido'
                    ])
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'finalizado',
                        'warning' => 'suspendido'
                    ]),
            ])
            ->filters([
                SelectFilter::make('estado_alquiler')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activos',
                        'finalizado' => 'Finalizados',
                        'suspendido' => 'Suspendidos'
                    ]),

                Filter::make('vencidos')
                    ->label('Pagos Vencidos')
                    ->query(fn (Builder $query): Builder => $query->where('fecha_proximo_pago', '<', Carbon::now())),

                Filter::make('activos')
                    ->label('Solo Activos')
                    ->query(fn (Builder $query): Builder => $query->where('estado_alquiler', 'activo')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('fecha_proximo_pago', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlquileres::route('/'),
            'create' => Pages\CreateAlquileres::route('/create'),
            'edit' => Pages\EditAlquileres::route('/{record}/edit'),
        ];
    }
    // REMOVER ESTAS LÍNEAS:
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['id_usuario_creador'] = Auth::id();
    //     return $data;
    // }
}

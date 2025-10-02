<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentosResource\Pages;
use App\Models\Departamento;
use App\Models\Edificio;
use App\Models\EstadoDepartamento;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class DepartamentosResource extends Resource
{
    protected static ?string $model = Departamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Departamentos');
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
                    Section::make('Información Básica')
                        ->schema([
                            Select::make('id_edificio')
                                ->label('Edificio')
                                ->options(function () {
                                    return Edificio::query()
                                        ->where('activo', true)
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id_edificio');
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive(),
                            TextInput::make('numero_departamento')
                                ->label('Número/Código')
                                ->required()
                                ->maxLength(20)
                                ->rules([
                                    function ($get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $edificioId = $get('id_edificio');
                                            if (!$edificioId) return;

                                            $exists = \App\Models\Departamento::where('numero_departamento', $value)
                                                ->where('id_edificio', $edificioId)
                                                ->when($get('id_departamento'), function ($query, $id) {
                                                    return $query->where('id_departamento', '!=', $id);
                                                })
                                                ->exists();

                                            if ($exists) {
                                                $fail('Ya existe un departamento con este número/código en el edificio seleccionado.');
                                            }
                                        };
                                    }
                                ]),
                            TextInput::make('piso')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),

                            Select::make('id_estado_departamento')
                                ->label('Estado')
                                ->options(function () {
                                    return EstadoDepartamento::query()
                                        ->where('activo', true)
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id_estado_departamento');
                                })
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])->columns(2),

                    Section::make('Características')
                        ->schema([
                            TextInput::make('cuartos')
                                ->numeric()
                                ->minValue(1)
                                ->required(),

                            TextInput::make('banos')
                                ->label('Baños')
                                ->numeric()
                                ->minValue(1)
                                ->required(),

                            TextInput::make('metros_cuadrados')
                                ->label('Metros Cuadrados')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0),

                            TextInput::make('precio_alquiler')
                                ->label('Precio de Alquiler')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required()
                                ->prefix('$'),
                        ])->columns(2),

                    Section::make('Información Adicional')
                        ->schema([
                            Textarea::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(1000)
                                ->rows(3)
                                ->columnSpanFull(),

                            FileUpload::make('foto_path')
                                ->label('Foto del Departamento')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                ->directory('departamentos')
                                ->maxSize(5120) // 5MB
                                ->image()
                                ->imageResizeMode('cover')
                                ->imageResizeTargetWidth('1920')
                                ->imageResizeTargetHeight('1080')
                                ->columnSpanFull(),


                            Toggle::make('activo')
                                ->label('Departamento Activo')
                                ->default(true)
                                ->inline(false),
                        ])->columns(1),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*
                TextColumn::make('id_departamento')
                    ->label('#')
                    ->sortable(),
                */
                ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->square()
                    ->size(50)
                    ->toggleable(),

                TextColumn::make('edificio.nombre')
                    ->label('Edificio')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('numero_departamento')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('piso')
                    ->sortable(),

                TextColumn::make('cuartos')
                    ->sortable(),

                TextColumn::make('banos')
                    ->label('Baños')
                    ->sortable(),

                TextColumn::make('precio_alquiler')
                    ->label('Precio')
                    ->money('PEN', true)
                    ->sortable(),

                BadgeColumn::make('estado.nombre')
                    ->label('Estado')
                    ->colors([
                        'success' => 'Disponible',
                        'danger' => 'Ocupado',
                        'warning' => 'Mantenimiento',
                        'primary' => 'Reservado',
                        'secondary' => 'Desocupado',
                    ]),

                BadgeColumn::make('activo')
                    ->label('Activo')
                    ->enum([
                        true => 'Sí',
                        false => 'No'
                    ])
                    ->colors([
                        'success' => true,
                        'danger' => false
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('id_edificio')
                    ->label('Edificio')
                    ->options(function () {
                        return Edificio::query()
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id_edificio');
                    })
                    ->searchable(),

                SelectFilter::make('id_estado_departamento')
                    ->label('Estado')
                    ->options(function () {
                        return EstadoDepartamento::query()
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id_estado_departamento');
                    })
                    ->searchable(),

                SelectFilter::make('activo')
                    ->label('Estado del Departamento')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos'
                    ]),
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

               // Tables\Actions\ViewAction::make(),
              // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function (\Illuminate\Database\Eloquent\Collection $records, $action) {
                        // Validar que ningún departamento tenga alquileres activos
                        $departamentosConAlquileres = [];

                        foreach ($records as $departamento) {
                            if ($departamento->tieneAlquilerActivo()) {
                                $departamentosConAlquileres[] = "'{$departamento->numero_departamento}' (Edificio: {$departamento->edificio->nombre})";
                            }
                        }

                        if (!empty($departamentosConAlquileres)) {
                            \Filament\Notifications\Notification::make()
                                ->title('No se pueden eliminar algunos departamentos')
                                ->body('Los siguientes departamentos tienen alquileres activos y no pueden ser eliminados: ' . implode(', ', $departamentosConAlquileres) . '. Debe finalizar primero todos los alquileres.')
                                ->danger()
                                ->duration(5000)
                                ->send();

                            // Cancelar la acción y cerrar el modal
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\DisponibilidadPorPisos::route('/'),
            'create' => Pages\CreateDepartamentos::route('/create'),
            'edit' => Pages\EditDepartamentos::route('/{record}/edit'),
            'listado' => Pages\ListDepartamentos::route('/listado'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario_creador'] = Auth::id();
        return $data;
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteAlquilerResource\Pages;
use App\Models\ClienteAlquiler;
use App\Models\TipoDocumento;
use App\Models\Ruta;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Session;

class ClienteAlquilerResource extends Resource
{
    protected static ?string $model = ClienteAlquiler::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Clientes Alquiler');
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
                    // Sección 1: Información personal
                    Section::make('Información Personal')
                        ->schema([
                            Select::make('id_tipo_documento')
                                ->label('Tipo de Documento')
                                ->options(function () {
                                    return TipoDocumento::query()
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id_tipo_documento');
                                })
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('numero_documento')
                                ->label('No. de Documento')
                                ->required()
                                ->maxLength(20),

                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('apellido')
                                ->required()
                                ->maxLength(100),
                        ])->columns(2),

                    // Sección 2: Información de contacto
                    Section::make('Información de Contacto')
                        ->schema([
                            TextInput::make('celular')
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('telefono')
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('direccion')
                                ->label('Dirección')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('direccion2')
                                ->label('Dirección 2')
                                ->maxLength(255),

                            TextInput::make('ciudad')
                                ->maxLength(100),

                            TextInput::make('nombre_negocio')
                                ->label('Nombre del Negocio')
                                ->maxLength(255),


                                  Toggle::make('activo')
                                ->label('Cliente Activo')
                                ->default(true)
                                ->inline(false),
                        ])->columns(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_cliente_alquiler')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(['nombre', 'apellido'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->searchable(),

                TextColumn::make('tipoDocumento.nombre')
                    ->label('Tipo Doc.')
                    ->sortable(),

                TextColumn::make('celular')
                    ->searchable(),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('ruta.nombre')
                    ->label('Ruta')
                    ->sortable(),

                BadgeColumn::make('activo')
                    ->label('Estado')
                    ->enum([
                        true => 'Activo',
                        false => 'Inactivo'
                    ])
                    ->colors([
                        'success' => true,
                        'danger' => false
                    ]),
            ])
            ->filters([
                SelectFilter::make('id_ruta')
                    ->label('Ruta')
                    ->options(function () {
                        return Ruta::query()
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id_ruta');
                    })
                    ->searchable(),

                SelectFilter::make('activo')
                    ->label('Estado del Cliente')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($action, $records) {
                        // Verificar si algún cliente tiene alquileres activos
                        $clientesConAlquilerActivo = $records->filter(function ($cliente) {
                            return $cliente->tieneAlquilerActivo();
                        });

                        if ($clientesConAlquilerActivo->isNotEmpty()) {
                            $nombres = $clientesConAlquilerActivo->pluck('nombre_completo')->join(', ');
                            
                            \Filament\Notifications\Notification::make()
                                ->title('No se puede eliminar')
                                ->body("Los siguientes clientes tienen alquileres activos y no pueden ser eliminados: {$nombres}")
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
            'index' => Pages\ListClienteAlquiler::route('/'),
            'create' => Pages\CreateClienteAlquiler::route('/create'),
            'edit' => Pages\EditClienteAlquiler::route('/{record}/edit'),
           // 'view' => Pages\ViewClienteAlquiler::route('/{record}'),
        ];
    }
}

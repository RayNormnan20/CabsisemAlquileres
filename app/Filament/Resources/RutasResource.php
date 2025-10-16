<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RutasResource\Pages;
use App\Models\Oficina;
use App\Models\Ruta;
use App\Models\TipoCobro;
use App\Models\TipoDocumento;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class RutasResource extends Resource
{
    protected static ?string $model = Ruta::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Rutas');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Configuración');
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Card::make()->schema([
                // Sección 1: Información básica
                Section::make('Información Básica')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre de la Ruta *'),



                        Select::make('id_oficina')
                            ->label('Oficina')
                            ->options(Oficina::all()->pluck('nombre', 'id_oficina'))
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                // Sección 2: Configuraciones de documentos y cobro
                Section::make('Configuraciones')
                    ->schema([
                        Select::make('id_tipo_documento')
                            ->label('Tipo de documento')
                            ->options(TipoDocumento::all()->pluck('nombre', 'id_tipo_documento'))
                            ->searchable(),

                        Select::make('id_tipo_cobro')
                            ->label('Tipo de Cobro')
                            ->options(TipoCobro::all()->pluck('nombre', 'id_tipo_cobro'))
                            ->searchable(),

                        Toggle::make('agregar_ceros_cantidades')
                            ->label('Agrupar 2 ceros a las cantidades del sistema')
                            ->inline(false),
                    ])->columns(2),

                // Sección 3: Configuración de créditos
                Section::make('Configuración de Créditos')
                    ->schema([
                        TextInput::make('porcentajes_credito')
                            ->label('Limitar el porcentaje para créditos')
                            ->helperText('Agregue uno o más porcentajes separados por comas. Ejemplo: 20,24,30')
                            ->regex('/^[\d,]+$/')
                            ->maxLength(255),

                             Toggle::make('activa')
                            ->label('Ruta activa')
                            ->default(true)
                            ->inline(false),

                    ])->columns(2),

                // En el formulario
                // En App\Filament\Resources\RutasResource

                Select::make('usuarios') // Cambiado de 'usuario' a 'usuarios'
                    ->relationship('usuarios', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required(),



                Hidden::make('creada_en')
                    ->default(now()->toDateString()),
            ])
        ]);
}
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('oficina.nombre')
                    ->label('Oficina')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('creada_en')
                    ->label('Creada')
                    ->date('d M Y')
                    ->sortable(),

                BadgeColumn::make('activa')
                    ->label('Estado')
                    ->enum([
                        true => 'Abierta',
                        false => 'Cerrada'
                    ])
                    ->colors([
                        'success' => true,
                        'danger' => false
                    ]),
            ])
            ->filters([
                SelectFilter::make('id_oficina')
                    ->label('Oficina')
                    ->options(Oficina::all()->pluck('nombre', 'id_oficina'))
                    ->searchable(),

                SelectFilter::make('activa')
                    ->label('Estado')
                    ->options([
                        true => 'Abiertas',
                        false => 'Cerradas'
                    ]),
            ])
            ->actions([
                Action::make('toggle_status')
                    ->label(fn (Ruta $record) => $record->activa ? 'Cerrar Ruta' : 'Abrir Ruta')
                    ->color(fn (Ruta $record) => $record->activa ? 'danger' : 'success')
                    ->icon(fn (Ruta $record) => $record->activa ? 'heroicon-s-lock-closed' : 'heroicon-s-lock-open')
                    ->action(function (Ruta $record) {
                        $record->activa = !$record->activa;
                        $record->save();
                    }),

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


            ])
            ->bulkActions([]);


    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRutas::route('/'),
            'create' => Pages\CreateRutas::route('/create'),
            'edit' => Pages\EditRutas::route('/{record}/edit'),
        ];
    }
}

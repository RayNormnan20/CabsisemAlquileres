<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OficinasResource\Pages;
use App\Models\Oficina;
use App\Models\Ruta;
use App\Models\Moneda;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Split;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class OficinasResource extends Resource
{
    protected static ?string $model = Oficina::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Oficinas');
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
            Section::make('Información Básica')
                ->schema([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(255)
                        ->label('Nombre de la Oficina *')
                        ->columnSpan(2),

                    Select::make('id_moneda')
                        ->label('Tipo de Moneda')
                        ->options(Moneda::where('activa', true)->pluck('nombre', 'id_moneda'))
                        ->required()
                        ->searchable(),

                    TextInput::make('pais')
                        ->required()
                        ->maxLength(255)
                        ->label('País'),

                    TextInput::make('codigo')
                       // ->required()
                        ->maxLength(255)
                        ->label('Código'),
                ])->columns(3),

            Section::make('Configuración de Abonos')
                ->schema([
                    Select::make('max_abonos_diarios')
                    ->label('No. máximo de abonos por cliente/día *')
                     ->options([
                        '0' => 'Sin límite',
                        '1' => '1 Abono',
                        '2' => '2 Abonos',
                        '3' => '3 Abonos',
                        '4' => '4 Abonos',
                        '5' => '5 Abonos',
                        '6' => '6 Abonos',
                        '7' => '7 Abonos',
                        '8' => '8 Abonos',
                        '9' => '9 Abonos',
                        '10' => '10 Abonos',
                        '11' => '11 Abonos',
                        '12' => '12 Abonos',
                        '13' => '13 Abonos',
                        '14' => '14 Abonos',
                        '15' => '15 Abonos',
                        '16' => '16 Abonos',
                        '17' => '17 Abonos',
                        '18' => '18 Abonos',
                        '19' => '19 Abonos',
                        '20' => '20 Abonos',
                    ])
                                    ->default('0') // 'Sin límite' como valor por defecto
                    ->required()
                    ->helperText('Número máximo de abonos que un cliente puede dar en un día'),

                    TextInput::make('porcentajes_credito')
                        ->label('Porcentajes para créditos')
                        ->required()
                        ->helperText('Agregar uno o más porcentajes separados por comas. Ejemplo: 20,24,30')
                        ->regex('/^[\d,]+$/')
                        ->maxLength(255),

                    Toggle::make('activar_seguros')
                        ->label('Activar Seguros')
                        ->inline(false),
                ])->columns(2),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('nombre')
                        ->label('Nombre de la Oficina')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->size('lg'),

                    TextColumn::make('pais')
                        ->label('País')
                        ->searchable()
                        ->sortable()
                        ->color('gray')
                        ->icon('heroicon-o-location-marker')
                        ->iconPosition('after'),
                ])
                ->extraAttributes(['class' => 'px-4 py-3']),

                Split::make([
                    TextColumn::make('rutas_count')
                        ->label('Rutas Asociadas')
                        ->getStateUsing(fn (Oficina $record) => Ruta::where('id_oficina', $record->id_oficina)->count())
                        ->sortable()
                        ->alignCenter()
                        ->size('lg')
                        ->weight('bold')
                        ->color('primary')
                        ->extraAttributes(['class' => 'bg-primary-50 rounded-lg p-2']),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                SelectFilter::make('pais')
                    ->options(
                        Oficina::query()->pluck('pais', 'pais')->unique()->toArray()
                    )
                    ->label('Filtrar por País')
                    ->searchable()
                    ->multiple(),
            ])
           ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil')
                    ->color('primary')
                    ->button()
                    ->size('sm')
                    ->tooltip('Editar oficina'),

                Tables\Actions\Action::make('ver_rutas')
                    ->label('Rutas')
                    ->icon('heroicon-s-map')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->tooltip('Ver rutas asociadas')
                    ->url(fn (Oficina $record): string => route('filament.resources.rutas.index', [
                        'tableFilters' => [
                            'id_oficina' => [
                                'value' => $record->id_oficina
                            ]
                        ]
                    ])),
            /*
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash')
                    ->color('danger')
                    ->button()
                    ->size('sm')
                    ->tooltip('Eliminar oficina')
                    */
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOficinas::route('/'),
            'create' => Pages\CreateOficinas::route('/create'),
            'edit' => Pages\EditOficinas::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EdificiosResource\Pages;
use App\Models\Edificio;
use App\Models\ClienteAlquiler;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class EdificiosResource extends Resource
{
    protected static ?string $model = Edificio::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Edificios');
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
                    Section::make('Información del Edificio')
                        ->schema([
                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(100),


                            TextInput::make('numero_pisos')
                                ->label('Número de Pisos')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(50)
                                ->required(),

                            Toggle::make('activo')
                                ->label('Edificio Activo')
                                ->default(true)
                                ->inline(false),
                        ])->columns(2),

                    Section::make('Ubicación')
                        ->schema([
                            TextInput::make('direccion')
                                ->label('Dirección')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('ciudad')
                                ->maxLength(100),

                            Textarea::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(1000)
                                ->rows(3)
                                ->columnSpanFull(),
                        ])->columns(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_edificio')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                    /*
                TextColumn::make('propietario.nombre_completo')
                    ->label('Propietario')
                    ->searchable(['propietario.nombre', 'propietario.apellido'])
                    ->sortable(),
*/
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('ciudad')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('numero_pisos')
                    ->label('Pisos')
                    ->sortable(),

                TextColumn::make('departamentos_count')
                    ->label('Departamentos')
                    ->counts('departamentos')
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

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('id_cliente_alquiler')
                    ->label('Propietario')
                    ->options(function () {
                        return ClienteAlquiler::query()
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->get()
                            ->mapWithKeys(fn($c) => [$c->id_cliente_alquiler => $c->nombre_completo]);
                    })
                    ->searchable(),

                SelectFilter::make('activo')
                    ->label('Estado del Edificio')
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
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEdificios::route('/'),
            'create' => Pages\CreateEdificios::route('/create'),
            'edit' => Pages\EditEdificios::route('/{record}/edit'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario_creador'] = Auth::id();
        return $data;
    }
}
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstadoDepartamentoResource\Pages;
use App\Models\EstadoDepartamento;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;

class EstadoDepartamentoResource extends Resource
{
    protected static ?string $model = EstadoDepartamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Estados Departamento');
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
                    Section::make('Información del Estado')
                        ->schema([
                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true),

                            Textarea::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(500)
                                ->rows(3),

                            ColorPicker::make('color')
                                ->label('Color para UI')
                                ->default('#6B7280')
                                ->required(),

                            Toggle::make('activo')
                                ->label('Estado Activo')
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
                /*
                TextColumn::make('id_estado_departamento')
                    ->label('#')
                    ->sortable(),
                    */

                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),

                ColorColumn::make('color')
                    ->label('Color'),

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
                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstadoDepartamentos::route('/'),
            'create' => Pages\CreateEstadoDepartamento::route('/create'),
            'edit' => Pages\EditEstadoDepartamento::route('/{record}/edit'),
        ];
    }
}

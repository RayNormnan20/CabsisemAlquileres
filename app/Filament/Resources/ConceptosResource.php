<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConceptosResource\Pages;
use App\Filament\Resources\ConceptosResource\RelationManagers;
use App\Models\Concepto;
use App\Models\Conceptos;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\IconColumn;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConceptosResource extends Resource
{
    protected static ?string $model = Concepto::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Conceptos');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Permissions');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre del Concepto')
                    ->required()
                    ->maxLength(50)
                    ->unique(table: 'conceptos', column: 'nombre')
                    ->placeholder('Ej: Arriendo, Seguros, Batería'),
                    
                Select::make('tipo')
                    ->label('Tipo')
                    ->required()
                    ->options([
                        'Ingresos' => 'Ingresos',
                        'Gastos' => 'Gastos'
                    ])
                    ->default('Gastos') 
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Concepto')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('tipo')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'Ingresos',
                        'danger' => 'Gastos',
                    ])
                    ->sortable(),
                    
                IconColumn::make('acciones')
                    ->label('Acciones')
                    ->options([
                        'heroicon-o-pencil-alt' => fn ($state): bool => true,
                    ])
                    ->color('primary')
                    ->size('lg')
                    ->extraAttributes([
                        'title' => 'Editar',
                        'data-tooltip' => 'Editar'
                    ]),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'Ingresos' => 'Ingresos',
                        'Gastos' => 'Gastos',
                    ]),
            ])
            ->actions([]) // Esto deshabilita completamente la columna de acciones
            ->defaultSort('nombre', 'asc');
    }
        
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConceptos::route('/'),
            'create' => Pages\CreateConceptos::route('/create'),
            'edit' => Pages\EditConceptos::route('/{record}/edit'),
        ];
    }    
}

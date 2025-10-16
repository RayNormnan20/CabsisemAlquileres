<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuadreRecaudadorResource\Pages;
use App\Filament\Resources\CuadreRecaudadorResource\RelationManagers;
use App\Models\CuadreRecaudador;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuadreRecaudadorResource extends Resource
{
    protected static ?string $model = CuadreRecaudadorResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'Cuadre/recaudador';
    protected static ?string $navigationLabel = 'Cuadre Recaudador';
    protected static ?string $modelLabel = 'Reporte';
    protected static ?string $pluralModelLabel = 'Cuadre Recaudador';
    protected static ?string $navigationGroup = 'Reportes';

    // Esta línea oculta el recurso completamente del menú
    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuadreRecaudadors::route('/'),
            'create' => Pages\CreateCuadreRecaudador::route('/create'),
            'edit' => Pages\EditCuadreRecaudador::route('/{record}/edit'),
        ];
    }
}

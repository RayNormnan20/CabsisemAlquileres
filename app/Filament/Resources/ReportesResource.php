<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportesResource\Pages;
use App\Filament\Resources\ReportesResource\RelationManagers;
use App\Models\Reportes;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportesResource extends Resource
{
    protected static ?string $model = Reportes::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
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
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
        'index' => Pages\ListReportes::route('/'),
        'cuadre' => Pages\CuadreRecaudador::route('/cuadre-recaudador'),
        'planilla' => Pages\PlanillaRecaudador::route('/planilla-recaudador'),
      //  'promedio' => Pages\PromedioSemanal::route('/promedio-semanal'),
      //  'recorrido' => Pages\RecorridoRutaGps::route('/recorrido-ruta-gps'),
    ];
}

}

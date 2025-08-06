<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YapeClienteResource\Pages;
use App\Models\YapeCliente;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class YapeClienteResource extends Resource
{
    protected static ?string $model = YapeCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Yape Clientes';
    protected static ?string $modelLabel = 'Yape Cliente';
public static function form(Form $form): Form
{
    return $form->schema([
        // Campo oculto para el usuario logueado
        Forms\Components\Hidden::make('user_id')
            ->default(fn () => Auth::id()),

        // Solo el nombre del que yapea
        Forms\Components\TextInput::make('nombre')
            ->required()
            ->label('Nombre del que Yapea'),

        // Los dos campos numéricos
        Forms\Components\TextInput::make('monto')
            ->numeric()
            ->required()
            ->label('Monto'),

        Forms\Components\TextInput::make('entregar')
            ->numeric()
            ->label('Entregar'),
    ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Remover o modificar la columna de cliente ya que ahora puede ser null
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->default('Sin cliente asignado') // Mostrar texto por defecto cuando es null
                    ->visible(false), // O puedes ocultarla completamente

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre Yape')
                    ->searchable(),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Cobrador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('monto')
                    ->money('PEN', true)
                    ->label('Monto')
                    ->sortable(),

                Tables\Columns\TextColumn::make('entregar')
                    ->money('PEN', true)
                    ->label('Entregar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha de Registro')
                    ->sortable(),
            ])

            ->filters([
                // Filtros adicionales pueden ir aquí
                Tables\Filters\Filter::make('recientes')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYapeClientes::route('/'),
            'create' => Pages\CreateYapeCliente::route('/create'),
            'edit' => Pages\EditYapeCliente::route('/{record}/edit'),
        ];
    }
}

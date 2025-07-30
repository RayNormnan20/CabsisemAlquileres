<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConceptosAbonosResource\Pages;
use App\Models\ConceptoAbono;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;

use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use App\Models\User;


class ConceptosAbonosResource extends Resource
{
    protected static ?string $model = ConceptoAbono::class;
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Conceptos de Abonos';

   public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Hidden::make('id_usuario'),
            Forms\Components\TextInput::make('tipo_concepto')
                ->label('Tipo de concepto')
                ->default(fn () => request()->get('tipo'))
                ->disabled() // Para que no lo modifiquen
                ->dehydrated() // Para que se incluya en el payload
                ->required(),

            Forms\Components\TextInput::make('referencia')
                ->label('Referencia'),

            Forms\Components\TextInput::make('monto')
                ->numeric()
                ->required()
                ->label('Monto'),
        ]);
    }

   public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_concepto')->label('Tipo de concepto'),
                Tables\Columns\TextColumn::make('monto')->sortable(),
                Tables\Columns\TextColumn::make('referencia')->sortable()->searchable()->label('Observación'),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('usuario_id')
                    ->label('Filtrar por usuario')
                    ->relationship('usuario', 'name') // Asegúrate que 'usuario' sea la relación con User
                    ->searchable(),
            ])
            ->actions([
                Action::make('edit') // Usando la clase importada directamente
                        ->label('')
                        ->icon('heroicon-o-pencil-alt')
                        ->color('primary')
                        ->size('lg')
                        ->url(fn ($record): string => static::getUrl('edit', ['record' => $record]))
                        ->extraAttributes([
                            'title' => 'Editar',
                            'class' => 'hover:bg-primary-50 rounded-full'
                        ]),


                        DeleteAction::make()
                        ->label('')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'title' => 'Eliminar',
                            'class' => 'hover:bg-danger-50 rounded-full'
                        ])
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConceptosAbonos::route('/'),
            'create' => Pages\CreateConceptosAbonos::route('/create'),
            'edit' => Pages\EditConceptosAbonos::route('/{record}/edit'),
        ];
    }
}
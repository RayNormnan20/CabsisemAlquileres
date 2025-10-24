<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConceptosAbonosResource\Pages;
use App\Models\ConceptoAbono;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
use Filament\Forms\Components\DateTimePicker;

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

            DateTimePicker::make('fecha_concepto')
                ->label('Fecha del Concepto')
                ->default(now())
                ->displayFormat('d/m/Y')
                ->format('Y-m-d H:i:s')
                ->withoutTime()
                ->dehydrateStateUsing(fn ($state) => $state ? now()->format('Y-m-d H:i:s') : null)
                ->required(),

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
                Tables\Columns\TextColumn::make('fecha_concepto')
                    ->label('Fecha')
                    ->getStateUsing(fn ($record) => $record->fecha_concepto ?? $record->created_at)
                    ->date('d/m/Y')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('usuario_id')
                    ->label('Filtrar por usuario')
                    ->relationship('usuario', 'name') // Asegúrate que 'usuario' sea la relación con User
                    ->searchable(),
            ])
            ->actions([
                // Usar EditAction para respetar la Policy 'update'
                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('primary')
                    ->size('lg')
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

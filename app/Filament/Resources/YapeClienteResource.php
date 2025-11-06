<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YapeClienteResource\Pages;
use App\Models\YapeCliente;
use App\Filament\Resources\ClientesResource;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class YapeClienteResource extends Resource
{
    protected static ?string $model = YapeCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Yapes del día';
        protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Yape Cliente';

     protected static function getNavigationGroup(): ?string
    {
        return __('Movimientos');

    }
public static function form(Form $form): Form
{
    return $form->schema([
        Grid::make(12)->schema([
            Forms\Components\Select::make('id_cliente')
                ->label('Cliente')
                ->options(function () {
                    $rutaId = session('selected_ruta_id');

                    if (!$rutaId) {
                        return [];
                    }

                    return \App\Models\Clientes::listarPorRuta($rutaId);
                })
                ->searchable()
                ->required()
                ->hidden(fn () => !session('selected_ruta_id'))
                ->default(fn () => request()->query('cliente_id'))
                ->columnSpan(10),

            Placeholder::make('crear_cliente_btn')
                ->content(function () {
                    $url = ClientesResource::getUrl('create', [
                        'crear_credito' => 'false',
                        'return_to' => 'yape-clientes-create',
                    ]);
                    // Usamos clases utilitarias de Filament para que luzca como botón
                    return new HtmlString(
                        '<a href="'.$url.'" class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600" title="Crear cliente">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Cliente</span>
                        </a>'
                    );
                })
                ->disableLabel()
                ->columnSpan(2)
                ->visible(fn () => session('selected_ruta_id')),
        ]),

            Forms\Components\TextInput::make('nombre')
                ->required()
                ->label('Nombre del que Yapea'),

            Forms\Components\Select::make('user_id')
                ->label('Cobrador')
                ->options(function () {
                    // Obtener todos los usuarios que tienen rutas asignadas
                    return \App\Models\User::whereHas('rutas')
                        ->select('id', 'name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->default(fn () => Auth::id())
                ->hidden(fn () => !session('selected_ruta_id')),

            Forms\Components\TextInput::make('valor')
                ->numeric()
                ->required()
                ->label('Monto del Crédito')
                ->helperText('Valor total del crédito registrado'),

            Forms\Components\TextInput::make('monto')
                ->numeric()
                ->label('Monto a Entregar (Yape)')
                ->helperText('Monto específico del Yape que se debe entregar'),


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
                    ->sortable(),


                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre Yape')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cobrador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->money('PEN', true)
                    ->label('Préstamo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto')
                    ->money('PEN', true)
                    ->label('Por Yapear')
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
            ->bulkActions([]);
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
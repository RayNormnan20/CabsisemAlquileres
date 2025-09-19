<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Models\Clientes;
use App\Models\TipoDocumento;
use Filament\Forms;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Filament\Tables\Columns\ImageColumn; // Asegúrate de importar esto
use Illuminate\Support\HtmlString;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Livewire\TemporaryUploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ClientesResource extends Resource
{
    protected static ?string $model = Clientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Clientes');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Créditos');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    // Sección 1: Información personal
                    Section::make('Información Personal')
                        ->schema([
                            Select::make('id_tipo_documento')
                                ->label('Tipo de Documento')
                                ->options(function () {
                                    return TipoDocumento::query()
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id_tipo_documento');
                                })
                                ->required()
                                ->searchable()
                                ->preload(), // Esto mejora el rendimiento con muchos registros

                            TextInput::make('numero_documento')
                                ->label('No. de Documento')
                                ->required()
                                ->maxLength(20),

                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('apellido')
                                ->required()
                                ->maxLength(100),


                            // Campo para Foto 1 (requerida)
                           FileUpload::make('foto1_path')
                                ->label('Foto 1 del Cliente')
                                ->directory('clientes/fotos') // Carpeta donde se guardarán
                                ->disk('public')
                                ->image(),
                                //->required(),

                            FileUpload::make('foto2_path')
                                ->label('Foto 2 del Cliente')
                                ->directory('clientes/fotos')
                                ->disk('public')
                                ->image(),         // Opcional: Convertir a WebP para reducir peso
                        ])->columns(2),

                    // Sección 2: Información de contacto
                    Section::make('Información de Contacto')
                        ->schema([
                            TextInput::make('celular')
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('telefono')
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('direccion')
                                ->label('Dirección')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('direccion2')
                                ->label('Dirección 2')
                                ->maxLength(255),
                        ])->columns(2),

                    // Sección 3: Información adicional
                    Section::make('Información Adicional')
                        ->schema([
                            TextInput::make('ciudad')
                                ->maxLength(100),

                            TextInput::make('nombre_negocio')
                                ->maxLength(100),

                            Toggle::make('activo')
                                ->label('Cliente Activo')
                                ->default(true)
                                ->inline(false),

                            Toggle::make('crear_credito')
                                ->label('Crear crédito después de guardar')
                                ->default(true)
                                ->inline(false)
                                ->visible(fn (string $context) => $context === 'create'),

                        ])->columns(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*
                TextColumn::make('id_cliente')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                */
                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(['nombre', 'apellido'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->searchable(),
                /*
                TextColumn::make('nombre_negocio')
                    ->label('Negocio')
                    ->searchable(),
                */
                TextColumn::make('celular')
                    ->searchable(),

                 TextColumn::make('fotos')
                ->label('Fotos')
                ->formatStateUsing(function ($record) {
                    $html = '<div class="flex items-center space-x-1">';

                    if ($record->foto1_path) {
                        $html .= '<img src="'.asset('storage/'.$record->foto1_path).'" class="h-8 w-8 rounded-full object-cover border border-gray-200">';
                    }

                    if ($record->foto2_path) {
                        $html .= '<img src="'.asset('storage/'.$record->foto2_path).'" class="h-8 w-8 rounded-full object-cover border border-gray-200">';
                    }

                    $html .= '</div>';

                    return empty($record->foto1_path) && empty($record->foto2_path)
                        ? 'Sin fotos'
                        : new HtmlString($html);
                })
                ->sortable(false),

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
                /*
                ExportAction::make(),
                */
                Tables\Actions\Action::make('edit')
                    ->label('')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('primary')
                    ->size('lg')
                    ->url(fn ($record): string => static::getUrl('edit', ['record' => $record]))
                    ->extraAttributes([
                        'title' => 'Editar',
                        'class' => 'hover:bg-primary-50 rounded-full'
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->size('lg')
                    ->before(function ($record, $action) {
                        // Verificar si el cliente tiene créditos asignados
                        $totalCreditos = $record->creditos()->count();
                        if ($totalCreditos > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('No se puede eliminar')
                                ->body("No se puede eliminar el cliente {$record->nombre_completo} porque tiene créditos asignados.")
                                ->danger()
                                ->send();
                            
                            // Detener la eliminación y cerrar el modal
                            $action->cancel();
                            return;
                        }
                        
                        // Registrar la actividad ANTES de que el registro sea eliminado
                        \App\Models\LogActividad::registrar(
                            'Clientes', // Tipo de actividad
                            'Eliminó al cliente: ' . $record->nombre . ' ' . $record->apellido, // Mensaje
                            [ // Metadata adicional
                                'cliente_id' => $record->id_cliente,
                                'documento' => $record->numero_documento,
                                'nombre_completo' => $record->nombre . ' ' . $record->apellido,
                                'datos_eliminados' => $record->toArray() // Guarda una copia de los datos del cliente eliminado
                            ]
                        );
                    })
                    ->after(function () {
                        // Notificación de éxito después de eliminar
                        \Filament\Notifications\Notification::make()
                            ->title('Cliente eliminado exitosamente')
                            ->success()
                            ->send();
                    })
                    ->extraAttributes([
                        'title' => 'Eliminar',
                        'class' => 'hover:bg-danger-50 rounded-full'
                    ]),

                    Tables\Actions\Action::make('view_photos')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color(fn ($record) => $record->foto1_path || $record->foto2_path ? 'primary' : 'secondary')
                    ->size('sm')
                    ->button()
                    ->modalHeading('Fotos del Cliente')
                    ->form(function ($record) {
                        $components = [];

                        // Foto 1 si existe
                        if ($record->foto1_path) {
                            $imageUrl1 = asset('storage/'.$record->foto1_path);
                            $components[] = \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('foto1')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            <<<HTML
                                            <div class="space-y-1 p-2">
                                                <p class="text-xs font-medium text-gray-500">Foto 1</p>
                                                <div class="flex justify-center">
                                                    <img src="$imageUrl1"
                                                        class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer"
                                                        onclick="window.open(this.src, '_blank')">
                                                </div>
                                            </div>
                                            HTML
                                        ))
                                        ->disableLabel()
                                ])
                                ->columnSpanFull();
                        }

                        // Foto 2 si existe
                        if ($record->foto2_path) {
                            $imageUrl2 = asset('storage/'.$record->foto2_path);
                            $components[] = \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('foto2')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            <<<HTML
                                            <div class="space-y-1 p-2">
                                                <p class="text-xs font-medium text-gray-500">Foto 2</p>
                                                <div class="flex justify-center">
                                                    <img src="$imageUrl2"
                                                        class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer"
                                                        onclick="window.open(this.src, '_blank')">
                                                </div>
                                            </div>
                                            HTML
                                        ))
                                        ->disableLabel()
                                ])
                                ->columnSpanFull();
                        }

                        // Mensaje si no hay fotos
                        if (empty($components)) {
                            $components[] = \Filament\Forms\Components\Placeholder::make('no_photos')
                                ->content('No hay fotos disponibles')
                                ->disableLabel();
                        }

                        return $components;
                    })
                    ->modalWidth('xl')
                    ->modalButton('Cerrar')
                    ->hidden(fn ($record) => !$record->foto1_path && !$record->foto2_path)
                    ->extraAttributes([
                        'title' => 'Ver Fotos',
                        'class' => 'hover:bg-success-50 rounded-full'
                    ])
                ->action(function () {
                    // Acción vacía necesaria para el modal
                })
            ])
            
            ->bulkActions([
                // Eliminación masiva deshabilitada por seguridad
                // La eliminación masiva de clientes está comentada para evitar eliminaciones accidentales
                // Si se necesita habilitar en el futuro, descomentar el código siguiente:
                
                /*
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, $action) {
                        // Verificar si algún cliente tiene créditos asignados
                        foreach ($records as $cliente) {
                            $totalCreditos = $cliente->creditos()->count();
                            if ($totalCreditos > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No se puede eliminar')
                                    ->body("No se puede eliminar el cliente {$cliente->nombre_completo} porque tiene créditos asignados.")
                                    ->danger()
                                    ->send();
                                
                                // Detener la acción y cerrar el modal
                                $action->cancel();
                                return;
                            }
                        }
                        
                        // Registrar la actividad para cada cliente que será eliminado
                        foreach ($records as $cliente) {
                            \App\Models\LogActividad::registrar(
                                'Clientes', // Tipo de actividad
                                'Eliminó al cliente (eliminación masiva): ' . $cliente->nombre . ' ' . $cliente->apellido, // Mensaje
                                [ // Metadata adicional
                                    'cliente_id' => $cliente->id_cliente,
                                    'documento' => $cliente->numero_documento,
                                    'nombre_completo' => $cliente->nombre . ' ' . $cliente->apellido,
                                    'datos_eliminados' => $cliente->toArray(), // Guarda una copia de los datos del cliente eliminado
                                    'tipo_eliminacion' => 'masiva' // Indica que fue eliminación masiva
                                ]
                            );
                        }
                    })
                    ->after(function ($records) {
                        // Notificación de éxito después de eliminar
                        $cantidad = count($records);
                        \Filament\Notifications\Notification::make()
                            ->title("Se eliminaron {$cantidad} cliente(s) exitosamente")
                            ->success()
                            ->send();
                    }),
                */
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateClientes::route('/create'),
         //   'view' => Pages\ViewClientes::route('/{record}'),
            'edit' => Pages\EditClientes::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbonosResource\Pages;
use App\Models\Abonos;
use App\Models\LogActividad;
use Illuminate\Support\HtmlString;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AbonosResource extends Resource
{
    protected static ?string $model = Abonos::class;
    protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Abonos');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Movimientos');
    }

  public static function form(Form $form): Form
{

     $metodoPago = request('metodo_pago');
    return $form
        ->schema([
            // Sección de fechas y montos
            Forms\Components\Section::make('Datos del Abono')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('fecha_credito')
                                ->label('Fecha de Crédito')
                                ->disabled()
                                ->extraInputAttributes([
                                    'class' => 'bg-cyan-100 text-cyan-900',
                                    'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('fecha_vencimiento')
                                ->label('Fecha de Vencimiento')
                                ->disabled()
                                ->extraInputAttributes([
                                    'class' => 'bg-cyan-100 text-cyan-900',
                                    'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                ])
                                ->required(),

                            // Campo: Lista de nombres Yape del día (permite seleccionar y crear nuevos)
                            Forms\Components\Select::make('nombres_yape_del_dia')
                                ->label('Nombres Yape del Día')
                                ->options(function (callable $get, $livewire) {
                                    // Obtener todos los YapeClientes con nombres válidos
                                    $yapeClientes = \App\Models\YapeCliente::whereNotNull('nombre')
                                        ->where('nombre', '!=', '')
                                        ->with('abonos')
                                        ->get();

                                    $options = [];
                                    foreach ($yapeClientes as $yapeCliente) {
                                        // Calcular el total de abonos realizados para este YapeCliente
                                        $totalAbonos = $yapeCliente->abonos->sum('monto_abono');
                                        
                                        // Solo mostrar si el total de abonos es menor al monto objetivo
                                        if ($totalAbonos < $yapeCliente->monto) {
                                            $options[$yapeCliente->id] = $yapeCliente->nombre;
                                        }
                                    }
                                    
                                    // Ordenar las opciones
                                    asort($options);

                                    // En modo EDIT, incluir el YapeCliente seleccionado aunque no cumpla la condición
                                    if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                        $idYapeCliente = $get('id_yape_cliente');
                                        if ($idYapeCliente && !isset($options[$idYapeCliente])) {
                                            $yapeCliente = \App\Models\YapeCliente::find($idYapeCliente);
                                            if ($yapeCliente && $yapeCliente->nombre) {
                                                $options[$idYapeCliente] = $yapeCliente->nombre;
                                            }
                                        }
                                    }

                                    // Agregar el nombre del cliente como opción por defecto
                                    $clienteId = $get('id_cliente');
                                    if ($clienteId) {
                                        $cliente = \App\Models\Clientes::find($clienteId);
                                        if ($cliente) {
                                            $options['cliente_' . $clienteId] = $cliente->nombre_completo;
                                        }
                                    }

                                    return $options;
                                })
                                ->searchable()
                                ->allowHtml()
                                ->placeholder('Seleccionar nombre Yape')
                                ->dehydrated(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        // Verificar si es el cliente o un YapeCliente
                                        if (str_starts_with($state, 'cliente_')) {
                                            // Es el cliente
                                            $clienteId = str_replace('cliente_', '', $state);
                                            $cliente = \App\Models\Clientes::find($clienteId);
                                            if ($cliente) {
                                                $set('nombre_yape', $cliente->nombre_completo);
                                                $set('id_yape_cliente', null);
                                            }
                                        } else {
                                            // Es un YapeCliente
                                            $yapeCliente = \App\Models\YapeCliente::find($state);
                                            if ($yapeCliente) {
                                                $set('id_yape_cliente', $yapeCliente->id);
                                                $set('nombre_yape', $yapeCliente->nombre);
                                            }
                                        }
                                    } else {
                                        // Si no se selecciona nada, usar el nombre del cliente
                                        $clienteId = $get('id_cliente');
                                        if ($clienteId) {
                                            $cliente = \App\Models\Clientes::find($clienteId);
                                            if ($cliente) {
                                                $set('nombre_yape', $cliente->nombre_completo);
                                                $set('id_yape_cliente', null);
                                            }
                                        }
                                    }
                                })
                                ->afterStateHydrated(function ($state, callable $set, callable $get, $livewire) {
                                    // Lógica diferente para CREATE vs EDIT
                                    if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\CreateAbonos) {
                                        // En CREATE: siempre seleccionar el cliente por defecto
                                        $clienteId = $get('id_cliente');
                                        if ($clienteId && !$state) {
                                            $cliente = \App\Models\Clientes::find($clienteId);
                                            if ($cliente) {
                                                $set('nombres_yape_del_dia', 'cliente_' . $clienteId);
                                                $set('nombre_yape', $cliente->nombre_completo);
                                                $set('id_yape_cliente', null);
                                            }
                                        }
                                    } elseif ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                        // En EDIT: respetar la selección original
                                        $idYapeCliente = $get('id_yape_cliente');
                                        $clienteId = $get('id_cliente');

                                        if ($idYapeCliente) {
                                            // Hay un YapeCliente seleccionado, mostrarlo
                                            $yapeCliente = \App\Models\YapeCliente::find($idYapeCliente);
                                            if ($yapeCliente) {
                                                $set('nombres_yape_del_dia', $idYapeCliente);
                                                $set('nombre_yape', $yapeCliente->nombre);
                                            } else {
                                                // Si el YapeCliente no existe, usar el cliente
                                                if ($clienteId) {
                                                    $cliente = \App\Models\Clientes::find($clienteId);
                                                    if ($cliente) {
                                                        $set('nombres_yape_del_dia', 'cliente_' . $clienteId);
                                                        $set('nombre_yape', $cliente->nombre_completo);
                                                        $set('id_yape_cliente', null);
                                                    }
                                                }
                                            }
                                        } else {
                                            // No hay YapeCliente, mostrar el cliente
                                            if ($clienteId) {
                                                $cliente = \App\Models\Clientes::find($clienteId);
                                                if ($cliente) {
                                                    $set('nombres_yape_del_dia', 'cliente_' . $clienteId);
                                                    $set('nombre_yape', $cliente->nombre_completo);
                                                }
                                            }
                                        }
                                    }
                                })
                        ]),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('saldo_anterior')
                                ->label('Saldo')
                                ->numeric()
                                ->disabled()
                                ->extraInputAttributes([
                                    'class' => 'bg-cyan-100 text-cyan-900',
                                    'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                ])
                                ->prefix('S/'),

                            Forms\Components\TextInput::make('valor_cuota')
                                ->label('Cuota')
                                ->numeric()
                                ->disabled()
                                ->extraInputAttributes([
                                    'class' => 'bg-cyan-100 text-cyan-900',
                                    'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                ])
                                ->prefix('S/'),

                            TextInput::make('monto_abono')
                                ->label('Abono')
                                ->numeric()
                                ->required()
                                ->prefix('S/')
                                ->reactive()
                                ->extraInputAttributes([
                                    'class' => 'bg-cyan-100 text-cyan-900',
                                    'style' => 'background-color:rgb(114, 237, 241) !important; color:rgb(0, 0, 0) !important;'
                                ])
                                ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $i => $item) {
                                        $conceptos[$i]['monto'] = $state;
                                    }
                                    $set('conceptosabonos', $conceptos);
                                })
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $i => $item) {
                                        $conceptos[$i]['monto'] = $state;
                                    }
                                    $set('conceptosabonos', $conceptos);
                                }),



                        ]),
                ])
                ->columns(1),

            // Campos ocultos
            Forms\Components\Hidden::make('id_cliente')
                ->required(),

            Forms\Components\Hidden::make('id_credito'),
            Forms\Components\Hidden::make('id_ruta'),
            Forms\Components\Hidden::make('id_usuario'),
            Forms\Components\Hidden::make('saldo_posterior'),

            // Solo el ID se guarda automáticamente por el Select de arriba
            Forms\Components\Hidden::make('id_yape_cliente'),
            Forms\Components\Hidden::make('nombre_yape'),

            // Sección de métodos de pago
           Forms\Components\Section::make('Métodos de Pago')
                ->schema([
                    Repeater::make('conceptosabonos')
                        ->reactive()
                        ->label('')
                        ->relationship('conceptosabonos')
                        ->schema([
                            Select::make('tipo_concepto')
                                ->options(function ($livewire) {
                                    // Solo mostrar 4 opciones si estás en edición
                                     if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                        return [
                                            'Efectivo' => 'Efectivo',
                                            'Yape' => 'Yape',
                                            'Abono completar p.' => 'Abono completar p.',
                                            'Abono sin firma Chis' => 'Abono sin firma Chis',
                                        ];
                                    }

                                    // Mostrar todas en creación
                                    return [
                                        'Efectivo' => 'Efectivo',
                                        'Yape' => 'Yape',
                                        'Abono completar p.' => 'Abono completar p.',
                                        'Abono sin firma Chis' => 'Abono sin firma Chis',
                                        'otros egresos' => 'otros egresos',
                                        'otro ingresos' => 'otro ingresos',
                                        'Abono Sobrante COB' => 'Abono Sobrante COB',
                                        'Abono Faltante COB' => 'Abono Faltante COB',
                                        'Entrega Caja COBRADOR' => 'Entrega Caja COBRADOR',
                                        'Abono de Descuento' => 'Abono de Descuento',
                                    ];
                                })
                                ->required()
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => 1,
                                    'md' => 1
                                ])

                                ->disabled(function ($livewire) {
                                    if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\CreateAbonos) {
                                        return filled($livewire->metodo_pago);
                                    }
                                    /*
                                    if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                        return filled(optional($livewire->record)->conceptosabonos);
                                    }
                                    */
                                    return false;
                                }),

                            TextInput::make('monto')
                                ->label('Monto')
                                ->numeric()
                                ->required()
                                ->prefix('S/')
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => 1,
                                    'md' => 1
                                ])
                                ->disabled(true)
                                ->reactive()
                                ->suffixIcon('heroicon-s-exclamation')
                                ->extraAttributes([
                                    'class' => 'border-red-500 focus:border-red-500 focus:ring-red-500',
                                ]),



                            Forms\Components\FileUpload::make('foto_comprobante')
                            ->label('Comprobante')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']) // <- formatos aceptados
                            ->directory('comprobantes/abonos')
                            ->visible(fn ($get) => in_array($get('tipo_concepto'), ['Yape', 'Efectivo']))
                            ->required(fn ($get) => in_array($get('tipo_concepto'), ['Yape', 'Efectivo']))
                            ->columnSpan(2),

                            TextInput::make('referencia')
                                ->label('Observaciones')
                                ->visible(function ($get, $livewire) {
                                    return in_array($get('tipo_concepto'), ['Yape', 'Efectivo']) ||
                                        ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\CreateAbonos && $livewire->metodo_pago);
                                })
                                ->columnSpan(2)
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->minItems(1)
                        ->disableItemCreation()
                        //->createItemButtonLabel('Agregar método de pago')
                        ->visible(function ($get, $livewire) {
                            $tipo = $get('tipo_concepto');

                            if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\CreateAbonos) {
                                return in_array($tipo, ['Yape', 'Efectivo']) || filled($livewire->metodo_pago);
                            }

                            if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                $tieneConceptos = optional($livewire->record)->conceptosabonos->isNotEmpty();
                                return in_array($tipo, ['Yape', 'Efectivo', 'Abono completar p.', 'Abono sin firma Chis' ]) || $tieneConceptos;
                            }

                            return false;
                        })
                ]),


        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('fecha_pago')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i')
                ->sortable(),

            TextColumn::make('usuario.name')
                ->label('Usuario'),

            TextColumn::make('cliente.nombre_completo')
                ->label('Cliente')
                ->searchable(),

                TextColumn::make('concepto.nombre')
                    ->label('Concepto'),

                TextColumn::make('credito.tipoPago.nombre')
                    ->label('Forma de Pago')
                    ->searchable(),

                TextColumn::make('monto_abono')
                    ->label('Cantidad')
                    ->money('PEN', true)
                    ->sortable(),

                    Tables\Columns\TextColumn::make('conceptosabonos')
                    ->label('Detalle Entrega')
                    ->formatStateUsing(function ($record) {
                        return $record->conceptosabonos
                            ->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))
                            ->join(' | ');
                    })
                    ->wrap() // para que no se desborde si es muy largo
                    ->searchable(false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable(),

                Filter::make('fecha_pago') // Usando la clase importada directamente
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Action::make('Exportar')
                    ->icon('heroicon-o-document-download')
                    ->tooltip('Exportar Excel con imágenes')
                    ->action(fn ($livewire) => $livewire->exportExcel())
                    ->button(),
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

                Action::make('view') // Usando la clase importada directamente
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color(fn ($record) => $record->conceptosabonos->firstWhere('foto_comprobante', '!=', null) ? 'primary' : 'secondary')
                    ->size('sm')
                    ->button()
                    ->modalHeading('Detalles del Abono')
                    ->form(function ($record, $livewire) {
                        // Filtros desde la página principal
                        $clienteId = $livewire->clienteId;
                        $fechaDesde = $livewire->fechaDesde;
                        $fechaHasta = $livewire->fechaHasta;
                        $tipoConcepto = $livewire->tipoConcepto;
                        $rutaId = \Illuminate\Support\Facades\Session::get('selected_ruta_id');

                        // Query con filtros
                        $abonosQuery = Abonos::query()
                            ->with(['cliente', 'usuario', 'conceptosabonos'])
                            ->whereHas('conceptosabonos', function ($q) use ($tipoConcepto) {
                                $q->where('foto_comprobante', '!=', null);
                                if ($tipoConcepto) {
                                    $q->where('tipo_concepto', $tipoConcepto);
                                }
                            });

                        if ($clienteId) {
                            $abonosQuery->where('id_cliente', $clienteId);
                        }
                        if ($rutaId) {
                            $abonosQuery->whereHas('cliente', function ($q) use ($rutaId) {
                                $q->where('id_ruta', $rutaId);
                            });
                        }
                        if ($fechaDesde) {
                            $abonosQuery->whereDate('fecha_pago', '>=', $fechaDesde);
                        }
                        if ($fechaHasta) {
                            $abonosQuery->whereDate('fecha_pago', '<=', $fechaHasta);
                        } else {
                            // Si no hay filtros de fecha explícitos, aplicar el filtro de "hoy" por defecto,
                            // igual que en la tabla principal cuando no se eligen fechas.
                            $abonosQuery->whereDate('fecha_pago', \Carbon\Carbon::today()->format('Y-m-d'));
                        }

                        $abonosQuery->orderBy('fecha_pago', 'desc');

                        // Generar lista de comprobantes
                        $abonos = $abonosQuery->get()->map(function ($abono) {
                            // Lógica simplificada para yape_nombre
                            $yapeNombre = null;

                            // SOLO usar el nombre Yape si hay una selección específica (id_yape_cliente)
                            if ($abono->id_yape_cliente) {
                                $yapeNombre = $abono->yapeCliente->nombre ?? null;
                            }

                            // Si no hay id_yape_cliente pero hay nombre_yape, usarlo
                            if (!$yapeNombre && $abono->nombre_yape) {
                                $yapeNombre = $abono->nombre_yape;
                            }

                            // Si no hay selección específica, usar el nombre del cliente
                            if (!$yapeNombre) {
                                $yapeNombre = $abono->cliente->nombre_completo;
                            }

                            return [
                                'id' => $abono->id_abono,
                                'cliente' => $abono->cliente->nombre_completo,
                                'yape_nombre' => $yapeNombre,
                                'yape_id' => $abono->id_yape_cliente, // Nuevo campo con el ID
                                'fecha' => $abono->fecha_pago->format('d/m/Y H:i'),
                                'monto' => $abono->monto_abono,
                                'usuario' => $abono->usuario->name,
                                'metodos' => $abono->conceptosabonos->pluck('tipo_concepto')->implode(', '),
                                'url' => optional($abono->conceptosabonos->firstWhere('foto_comprobante', '!=', null))->foto_comprobante
                                    ? asset('storage/' . $abono->conceptosabonos->firstWhere('foto_comprobante', '!=', null)->foto_comprobante)
                                    : null,
                            ];
                        })->values();


                        $startIndex = $abonos->search(fn($a) => $a['id'] == $record->id_abono);

                        // Pasar todo a Alpine.js
                        $jsonData = htmlspecialchars($abonos->toJson(), ENT_QUOTES, 'UTF-8');

                            $html = <<<HTML
                            <div wire:ignore x-data="{
                                items: {$jsonData},
                                index: {$startIndex},
                                prev() { if (this.index > 0) this.index--; },
                                next() { if (this.index < this.items.length - 1) this.index++; }
                            }" class="space-y-4">

                                <!-- Navegación -->
                                <div class="flex justify-between items-center mb-4 bg-white p-3 rounded-lg shadow">
                                    <!-- Botón Anterior -->
                                    <button
                                        type="button"
                                        @click="prev"
                                        :disabled="index === 0"
                                        class="flex items-center space-x-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition"
                                    >
                                        <!-- Heroicon: Chevron Left -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        <span>Anterior</span>
                                    </button>

                                    <!-- Contador -->
                                    <span class="text-sm font-semibold text-gray-700">
                                        Comprobante <span x-text="index+1"></span> de <span x-text="items.length"></span>
                                    </span>

                                    <!-- Botón Siguiente -->
                                    <button
                                        type="button"
                                        @click="next"
                                        :disabled="index === items.length - 1"
                                        class="flex items-center space-x-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition"
                                    >
                                        <span>Siguiente</span>
                                        <!-- Heroicon: Chevron Right -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    </div>


                                <!-- Info -->
                                <div class="grid grid-cols-3 gap-2 text-xs p-2 bg-gray-50 rounded">
                                    <!-- Columna 1 -->
                                    <div>

                                        <p class="font-medium text-gray-500 mt-2">Usuario</p>
                                        <p x-text="items[index].usuario"></p>

                                        <p class="font-medium text-gray-500">Cliente</p>
                                        <p x-text="items[index].cliente"></p>
                                    </div>

                                    <!-- Columna 2 -->
                                    <div>
                                        <p class="font-medium text-gray-500">Fecha</p>
                                        <p x-text="items[index].fecha"></p>

                                        <p class="font-medium text-gray-500 mt-2">Nombre Yape</p>
                                        <p x-text="items[index].yape_nombre" class="text-red-600 font-bold"></p>
                                    </div>

                                    <!-- Columna 3 -->
                                    <div>
                                        <p class="font-medium text-gray-500 mt-2">Métodos de pago</p>
                                        <p x-text="items[index].metodos"></p>

                                        <p class="font-medium text-gray-500">Monto</p>
                                        <p>S/ <span x-text="items[index].monto"></span></p>
                                    </div>
                                </div>


                                <!-- Imagen -->
                                <template x-if="items[index].url">
                                    <div class="flex justify-center">
                                        <img :src="items[index].url" class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer"
                                            @click="window.open(items[index].url, '_blank')">
                                    </div>
                                </template>
                                <template x-if="!items[index].url">
                                    <p class="text-center text-gray-400">No hay comprobante disponible</p>
                                </template>
                            </div>
                        HTML;


                                return [
                                    Forms\Components\Placeholder::make('visor')
                                        ->content(new HtmlString($html))
                                        ->disableLabel()
                                ];
                            })
                        ->modalWidth('xl')
                        ->modalButton('Cerrar')
                        ->hidden(fn ($record) => $record->conceptosabonos->count() === 0)
                        ->extraAttributes([
                            'title' => 'Ver Comprobante',
                            'class' => 'hover:bg-success-50 rounded-full'
                        ])
                    ->action(function () {
                        // Acción vacía necesaria para el modal
                    }),
                    Action::make('delete')
                        ->label('')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->button()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Abono')
                        ->modalSubheading('¿Está seguro que desea eliminar este abono? Esta acción no se puede deshacer.')
                        ->modalButton('Sí, eliminar')
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                // Obtener datos para el log antes de eliminar
                                $clienteNombre = $record->cliente?->nombre . ' ' . $record->cliente?->apellido;
                                $rutaNombre = $record->ruta?->nombre ?? 'Ruta desconocida';

                                $credito = $record->credito()->lockForUpdate()->first();

                                if (! $credito) {
                                    throw new \Exception('Crédito asociado no encontrado.');
                                }
                                $credito->saldo_actual += $record->monto_abono;
                                $credito->save();

                                // Registrar log de actividad antes de eliminar
                                LogActividad::registrar(
                                    'Abonos',
                                    "Eliminó un abono de la ruta {$rutaNombre} para el cliente {$clienteNombre} del día " . $record->fecha_pago->format('d M Y') . " por S/" . number_format($record->monto_abono, 2),
                                    [
                                        'abono_id' => $record->id_abono,
                                        'cliente_id' => $record->id_cliente,
                                        'ruta_id' => $record->id_ruta,
                                        'fecha_pago' => $record->fecha_pago->format('Y-m-d'),
                                        'monto_abono' => $record->monto_abono
                                    ]
                                );

                                $record->delete();
                            });
                        })
                        ->extraAttributes([
                            'title' => 'Eliminar',
                            'class' => 'hover:bg-danger-50 rounded-full'
                        ])

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
            'index' => Pages\ListAbonos::route('/'),
            'create' => Pages\CreateAbonos::route('/create'),
            'edit' => Pages\EditAbonos::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbonosResource\Pages;
use App\Models\Abonos;
use App\Models\LogActividad;
use App\Settings\GeneralSettings;
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
                    Forms\Components\Grid::make(4)
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
                                ->required(function (callable $get) {
                                    // Requerido si hay al menos un concepto de tipo 'Yape'
                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $concepto) {
                                        if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                            return true;
                                        }
                                    }
                                    return false;
                                })
                                ->visible(function (callable $get) {
                                    // Solo mostrar si hay al menos un concepto de tipo 'Yape'
                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $concepto) {
                                        if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                            return true;
                                        }
                                    }
                                    return false;
                                })
                                ->options(function (callable $get, $livewire) {
                                    // Verificar si es una devolución
                                    $esDevolucion = $get('es_devolucion') ?? false;
                                    // Verificar si el checkbox está activo
                                    $mostrarSoloCompletados = $get('mostrar_solo_completados') ?? false;

                                    $options = [];

                                    if ($esDevolucion) {
                                         // Si es devolución, solo mostrar YapeClientes de este cliente que tengan exceso
                                         $clienteId = $get('id_cliente');

                                         if ($clienteId) {
                                             // Buscar YapeClientes del mismo cliente
                                             $yapeClientes = \App\Models\YapeCliente::whereNotNull('nombre')
                                                 ->where('nombre', '!=', '')
                                                 ->where('id_cliente', $clienteId)
                                                 ->with('abonos')
                                                 ->get();

                                            foreach ($yapeClientes as $yapeCliente) {
                                                // Calcular el yapeado real (abonos - devoluciones)
                                                $yapeadoReal = 0;
                                                foreach ($yapeCliente->abonos as $abono) {
                                                    if ($abono->es_devolucion) {
                                                        $yapeadoReal -= $abono->monto_abono;
                                                    } else {
                                                        $yapeadoReal += $abono->monto_abono;
                                                    }
                                                }
                                                $montoAjustado = $yapeCliente->monto;

                                                // Solo mostrar si tiene exceso
                                                if ($yapeadoReal > $montoAjustado) {
                                                    $exceso = $yapeadoReal - $montoAjustado;
                                                    $options[$yapeCliente->id] = $yapeCliente->nombre . ' (Exceso: S/' . number_format($exceso, 2) . ')';
                                                }
                                            }
                                        }
                                    } else {
                                        // Lógica para pagos regulares
                                        $yapeClientes = \App\Models\YapeCliente::whereNotNull('nombre')
                                            ->where('nombre', '!=', '')
                                            ->with('abonos')
                                            ->get();

                                        foreach ($yapeClientes as $yapeCliente) {
                                            // Calcular el yapeado real (abonos - devoluciones)
                                            $yapeadoReal = 0;
                                            foreach ($yapeCliente->abonos as $abono) {
                                                if ($abono->es_devolucion) {
                                                    $yapeadoReal -= $abono->monto_abono;
                                                } else {
                                                    $yapeadoReal += $abono->monto_abono;
                                                }
                                            }
                                            $montoAjustado = $yapeCliente->monto;

                                            if ($mostrarSoloCompletados) {
                                                // Si el checkbox está activo, mostrar solo completados o en exceso
                                                if ($yapeadoReal >= $montoAjustado) {
                                                    if ($yapeadoReal > $montoAjustado) {
                                                        $exceso = $yapeadoReal - $montoAjustado;
                                                        $options[$yapeCliente->id] = $yapeCliente->nombre;
                                                    } else {
                                                        $options[$yapeCliente->id] = $yapeCliente->nombre ;
                                                    }
                                                }
                                            } else {
                                                // Lógica normal: mostrar si el yapeado real es menor al monto objetivo
                                                if ($yapeadoReal < $montoAjustado) {
                                                    $options[$yapeCliente->id] = $yapeCliente->nombre;
                                                }
                                            }
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

                                    // NO agregar el nombre del cliente como opción
                                    // Solo mostrar nombres Yape del día

                                    return $options;
                                })
                                ->searchable()
                                ->allowHtml()
                                ->reactive()
                                ->placeholder('Seleccionar nombre Yape')
                                ->dehydrated(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        // Solo manejar YapeClientes
                                        $yapeCliente = \App\Models\YapeCliente::find($state);
                                        if ($yapeCliente) {
                                            $set('id_yape_cliente', $yapeCliente->id);
                                            $set('nombre_yape', $yapeCliente->nombre);
                                        }
                                    } else {
                                        // Si no se selecciona nada, limpiar los campos
                                        $set('id_yape_cliente', null);
                                        $set('nombre_yape', '');
                                    }
                                })
                                ->afterStateHydrated(function ($state, callable $set, callable $get, $livewire) {
                                    // Solo manejar YapeClientes en modo EDIT
                                    if ($livewire instanceof \App\Filament\Resources\AbonosResource\Pages\EditAbonos) {
                                        $idYapeCliente = $get('id_yape_cliente');

                                        if ($idYapeCliente) {
                                            // Hay un YapeCliente seleccionado, mostrarlo
                                            $yapeCliente = \App\Models\YapeCliente::find($idYapeCliente);
                                            if ($yapeCliente) {
                                                $set('nombres_yape_del_dia', $idYapeCliente);
                                                $set('nombre_yape', $yapeCliente->nombre);
                                            }
                                        }
                                    }
                                    // En CREATE no se preselecciona nada
                                }),

                                // Checkbox para filtrar nombres Yape completados/en exceso
                            Forms\Components\Checkbox::make('mostrar_solo_completados')
                                ->label('Mostrar solo nombres Yape completados o en exceso')
                                ->visible(function (callable $get) {
                                    // Verificar primero si la configuración general está habilitada
                                    try {
                                        $settings = app(GeneralSettings::class);
                                        if (!($settings->enable_yape_filter ?? true)) {
                                            return false;
                                        }
                                    } catch (\Exception $e) {
                                        // Si hay error al obtener settings, mostrar por defecto
                                    }

                                    // Solo mostrar si hay al menos un concepto de tipo 'Yape' Y NO es devolución
                                    $esDevolucion = $get('es_devolucion') ?? false;
                                    if ($esDevolucion) {
                                        return false;
                                    }

                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $concepto) {
                                        if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                            return true;
                                        }
                                    }
                                    return false;
                                })
                                ->reactive()
                                ->dehydrated(false),

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
                                ->minValue(0.01)
                                ->maxValue(function (callable $get) {
                                    $esDevolucion = (bool) ($get('es_devolucion') ?? false);
                                    return $esDevolucion ? null : (float) ($get('saldo_anterior') ?? 0);
                                })
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
                                    $state = round((float)$state, 2);
                                    $esDevolucion = (bool) ($get('es_devolucion') ?? false);
                                    $saldo = (float) ($get('saldo_anterior') ?? 0);
                                    if (!$esDevolucion && $state > $saldo) {
                                        $state = $saldo;
                                        $set('monto_abono', $saldo);
                                    }
                                    $conceptos = $get('conceptosabonos') ?? [];
                                    foreach ($conceptos as $i => $item) {
                                        $conceptos[$i]['monto'] = $state;
                                    }
                                    $set('conceptosabonos', $conceptos);
                                }),

                            Forms\Components\Toggle::make('es_devolucion')
                                ->label('Es Devolución')
                                ->helperText('Marcar si este registro es una devolución (no afectará el saldo del crédito)')
                                ->default(false)
                                ->reactive()
                                ->visible(function () {
                                    // Verificar si la configuración general está habilitada
                                    try {
                                        $settings = app(GeneralSettings::class);
                                        return $settings->enable_devolucion_filter ?? false;
                                    } catch (\Exception $e) {
                                        // Si hay error al obtener settings, no mostrar por defecto
                                        return false;
                                    }
                                }),

                            // Botón para abrir el historial de abonos del crédito actual
                            Forms\Components\Placeholder::make('btn_historial_abonos')
                                ->label('')
                                ->content(function (callable $get) {
                                    $clienteId = $get('id_cliente');
                                    if (!$clienteId) {
                                        return new HtmlString('');
                                    }

                                    $credito = \App\Models\Creditos::where('id_cliente', $clienteId)
                                        ->where('saldo_actual', '>', 0)
                                        ->first();

                                    if (!$credito) {
                                        return new HtmlString('');
                                    }

                                    $url = route('filament.resources.creditos.historial-credito', ['credito' => $credito->id_credito]);
                                    $classes = 'inline-flex items-center justify-center rounded-md bg-primary-600 text-white px-3 py-2 text-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500';
                                    $html = '<a href="' . $url . '" wire:navigate class="' . $classes . '" style="margin-top: 0.5rem;">Ver historial</a>';
                                    return new HtmlString($html);
                                })
                                ->columnSpan(1),

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
                                        'Efectivo CLi. No Regis.' => 'Efectivo CLi. No Regis.',
                                        'Entrega Caja COBRADOR' => 'Entrega Caja COBRADOR',
                                        'Abono de Descuento' => 'Abono de Descuento',
                                        'ENTREGA E.',
                                        'ABONO NO REGISTRADO'
                                    ];
                                })
                                ->required()
                                ->reactive()
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
                            ->disk('public')
                            ->visible(fn ($get) => in_array($get('tipo_concepto'), ['Yape', 'Efectivo']))
                            ->required(fn ($get) => $get('tipo_concepto') === 'Yape')
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

            // Sección de configuración adicional
            Forms\Components\Section::make('Configuración del Crédito')
                ->schema([
                    Forms\Components\Checkbox::make('activar_segundo_recorrido')
                        ->label('Activar Segundo Recorrido')
                        ->helperText('Al activar esta opción, el crédito será marcado automáticamente como "segundo recorrido" basado al crédito.')
                        ->visible(function () {
                            // Verificar si la configuración general está habilitada
                            try {
                                $settings = app(GeneralSettings::class);
                                return $settings->enable_segundo_recorrido_filter ?? false;
                            } catch (\Exception $e) {
                                // Si hay error al obtener settings, no mostrar por defecto
                                return false;
                            }
                        })
                        ->default(function (callable $get, $livewire) {
                            // En modo edición, usar el crédito del abono
                            if (isset($livewire->record)) {
                                $creditoId = $get('id_credito');
                                if ($creditoId) {
                                    $credito = \App\Models\Creditos::find($creditoId);
                                    return $credito ? (bool)$credito->segundo_recorrido : false;
                                }
                            }

                            // En modo creación, buscar el crédito activo del cliente
                            $clienteId = $get('id_cliente');
                            if ($clienteId) {
                                $credito = \App\Models\Creditos::where('id_cliente', $clienteId)
                                    ->where('saldo_actual', '>', 0)
                                    ->first();
                                return $credito ? (bool)$credito->segundo_recorrido : false;
                            }
                            return false;
                        })
                        ->reactive()
                        ->afterStateHydrated(function (callable $get, callable $set, $livewire) {
                            // Actualizar el estado del checkbox cuando se hidrata el formulario
                            if (!isset($livewire->record)) {
                                // En modo creación
                                $clienteId = $get('id_cliente');
                                if ($clienteId) {
                                    $credito = \App\Models\Creditos::where('id_cliente', $clienteId)
                                        ->where('saldo_actual', '>', 0)
                                        ->first();
                                    $set('activar_segundo_recorrido', $credito ? (bool)$credito->segundo_recorrido : false);
                                }
                            }
                        })
                        ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire) {
                            // Obtener el ID del crédito desde el formulario
                            $creditoId = $get('id_credito');

                            if ($creditoId && $state) {
                                // Actualizar el crédito inmediatamente cuando se activa el checkbox
                                \App\Models\Creditos::where('id_credito', $creditoId)
                                    ->update(['segundo_recorrido' => true]);

                                // Mostrar notificación
                                \Filament\Notifications\Notification::make()
                                    ->title('Segundo recorrido activado')
                                    ->body('El crédito ha sido marcado como segundo recorrido automáticamente.')
                                    ->success()
                                    ->send();
                            } elseif ($creditoId && !$state) {
                                // Desactivar segundo recorrido
                                \App\Models\Creditos::where('id_credito', $creditoId)
                                    ->update(['segundo_recorrido' => false]);

                                // Mostrar notificación
                                \Filament\Notifications\Notification::make()
                                    ->title('Segundo recorrido desactivado')
                                    ->body('El crédito ya no está marcado como segundo recorrido.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->columnSpanFull()
                ])
                ->columns(1),

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

                Tables\Columns\BadgeColumn::make('es_devolucion')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state ? 'Devolución' : 'Abono')
                    ->colors([
                        'success' => fn ($state) => !$state, // Verde para abonos normales
                        'danger' => fn ($state) => $state,   // Rojo para devoluciones
                    ])
                    ->sortable(),

                    Tables\Columns\TextColumn::make('conceptosabonos')
                    ->label('Detalle')
                    ->formatStateUsing(function ($record) {
                        return $record->conceptosabonos
                            ->map(fn($c) => $c->tipo_concepto)
                            ->join(' | ');
                    })
                    ->wrap() // para que no se desborde si es muy largo
                    ->searchable(false),

                Tables\Columns\TextColumn::make('conceptosabonos_monto')
                    ->label('Monto')
                    ->formatStateUsing(function ($record) {
                        return $record->conceptosabonos
                            ->map(fn($c) => "S/ " . number_format($c->monto, 2))
                            ->join(' | ');
                    })
                    ->wrap()
                    ->searchable(false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('es_devolucion')
                    ->label('Tipo de Registro')
                    ->options([
                        '0' => 'Abonos',
                        '1' => 'Devoluciones',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->where('es_devolucion', (bool) $data['value']);
                        }
                        return $query;
                    }),

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

                Action::make('Créditos')
                    ->icon('heroicon-o-collection')
                    ->tooltip('Ver créditos del cliente')
                    ->color('primary')
                    ->disabled(fn ($livewire) => !property_exists($livewire, 'clienteId') || blank($livewire->clienteId))
                    ->action(fn ($livewire) => method_exists($livewire, 'toggleCreditos') ? $livewire->toggleCreditos() : null)
                    ->button()

            ])
            ->actions([
                Action::make('edit') // Usando la clase importada directamente
                    ->label('')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('primary')
                    ->size('lg')
                    ->url(fn ($record): string => static::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => auth()->user()->can('update', $record))
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

                            // Si no hay selección específica, no mostrar nombre
                            // Solo mostrar nombres Yape válidos
                            if (!$yapeNombre) {
                                $yapeNombre = 'Sin nombre Yape';
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
                        ->visible(fn ($record) => auth()->user()->can('delete', $record))
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
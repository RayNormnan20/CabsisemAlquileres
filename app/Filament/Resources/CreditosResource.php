<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditosResource\Pages;
use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\OrdenCobro;
use App\Models\TipoPago;
use App\Models\YapeCliente;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Html;
use Filament\Forms\Components\Image as ImageComponent;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Livewire\TemporaryUploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class CreditosResource extends Resource
{
    protected static ?string $model = Creditos::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?int $navigationSort = 3;


    protected static function getNavigationLabel(): string
    {
        return __('Listar Creditos');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Créditos');
    }


    protected static function calculateCreditValues(
        float $valorCredito,
        float $porcentajeInteres,
        int $diasPlazo,
        ?string $formaPagoNombre,
        ?string $fechaCreditoStr,
        callable $set
    ): void {
        if ($valorCredito === null || $valorCredito <= 0 || $porcentajeInteres === null || $diasPlazo === null || $diasPlazo <= 0 || !$fechaCreditoStr) {
            $set('saldo_actual', null);
            $set('valor_cuota', null);
            $set('numero_cuotas', null);
            $set('fecha_vencimiento', null);
            $set('fecha_proximo_pago', null);
            return;
        }

        $valorTotal = $valorCredito * (1 + ($porcentajeInteres / 100));
        $set('saldo_actual', number_format($valorTotal, 2, '.', ''));

        $numeroCuotas = 0;
        switch (strtolower($formaPagoNombre)) {
            case 'diario':
                $numeroCuotas = $diasPlazo;
                break;
            case 'semanal':
                $numeroCuotas = ceil($diasPlazo / 7);
                break;
            case 'quincenal':
                $numeroCuotas = ceil($diasPlazo / 15);
                break;
            case 'mensual':
                $numeroCuotas = ceil($diasPlazo / 30);
                break;
            default:
                $numeroCuotas = $diasPlazo;
        }

        $numeroCuotas = max(1, $numeroCuotas);
        $set('numero_cuotas', (int) $numeroCuotas);

        $valorCuota = $valorTotal / $numeroCuotas;
        $set('valor_cuota', number_format($valorCuota, 2, '.', ''));

        $fechaCredito = Carbon::parse($fechaCreditoStr);
        $set('fecha_vencimiento', $fechaCredito->copy()->addDays($diasPlazo)->format('Y-m-d'));

        $nextPago = $fechaCredito->copy();
        switch (strtolower($formaPagoNombre)) {
            case 'diario':
                $nextPago->addDay();
                break;
            case 'semanal':
                $nextPago->addWeek();
                break;
            case 'quincenal':
                $nextPago->addDays(15);
                break;
            case 'mensual':
                $nextPago->addMonth();
                break;
            default:
                $nextPago->addDay();
        }
        $set('fecha_proximo_pago', $nextPago->format('Y-m-d'));
    }

    public static function form(Form $form): Form
    {

        $isAdicional = request()->query('tipo') === 'adicional';

        return $form
            ->schema([
                Card::make()->schema([
                    // Grid para dividir en dos columnas
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Hidden::make('es_adicional')
                            ->default(fn () => request()->query('tipo') === 'adicional')
                            ->reactive(),
                            // Columna izquierda (Datos de entrada)
                            Forms\Components\Group::make([
                                Forms\Components\Section::make('')
                                    ->schema([
                                        Select::make('id_cliente')
                                            ->label('Cliente')
                                            ->options(function () {
                                                $clienteId = request()->query('cliente_id');

                                                if ($clienteId) {
                                                    $cliente = Clientes::find($clienteId);
                                                    if ($cliente) {
                                                        return [$cliente->id_cliente => $cliente->nombre_completo];
                                                    }
                                                }

                                                return Clientes::query()
                                                    ->where('activo', true)
                                                    ->get()
                                                    ->pluck('nombre_completo', 'id_cliente');
                                            })
                                            ->default(function () {
                                                $clienteId = request()->query('cliente_id');
                                                return Clientes::where('activo', true)->where('id_cliente', $clienteId)->exists() ? $clienteId : null;
                                            })
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull(),

                                        DatePicker::make('fecha_credito')
                                            ->label('Fecha del Crédito')
                                            ->default(now())
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->reactive()

                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                static::calculateCreditValues($valorCredito, $porcentaje, $dias, $formaPagoNombre, $state, $set);
                                            }),

                                        TextInput::make('valor_credito')
                                            ->label('Valor del Crédito')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->columnSpanFull()
                                            ->placeholder('Ingrese el valor del crédito')

                                            ->helperText('Por favor ingresa el valor de Crédito')
                                            ->reactive()
                                            ->extraInputAttributes([
                                                'class' => 'bg-cyan-100 text-cyan-900',
                                                'style' => 'background-color:rgb(114, 237, 241) !important; color:rgb(0, 0, 0) !important;'
                                            ])
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $dias = (int) $get('dias_plazo');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues((float) $state, $porcentaje, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }),

                                        TextInput::make('porcentaje_interes') // reutilizado como cuota diaria si es adicional
                                            ->label(fn (callable $get) => $get('es_adicional') ? 'Cuota Diaria' : 'Porcentaje')
                                            ->numeric()
                                            ->required()
                                            ->default(10) // Valor por defecto: 10%
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->columnSpanFull()
                                            ->helperText(fn (callable $get) => $get('es_adicional') ? 'Ingresa el monto fijo por cuota diaria' : null)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) use ($isAdicional) {
                                                if (!$get('es_adicional')) {
                                                    $valorCredito = (float) $get('valor_credito');
                                                    $dias = (int) $get('dias_plazo');
                                                    $formaPagoId = $get('forma_pago');
                                                    $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                    $fechaCreditoStr = $get('fecha_credito');
                                                    static::calculateCreditValues($valorCredito, (float) $state, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                                }
                                            }),


                                        Select::make('forma_pago')
                                            ->label('Forma de Pago')
                                            ->options(TipoPago::where('activo', true)->pluck('nombre', 'id_forma_pago'))
                                            ->default(function () {
                                                return TipoPago::where('nombre', 'Diario')->value('id_forma_pago');
                                            })
                                            ->disabled($isAdicional)
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) use ($isAdicional) {
                                                if (!$isAdicional) {
                                                    $valorCredito = (float) $get('valor_credito');
                                                    $porcentaje = (float) $get('porcentaje_interes');
                                                    $dias = (int) $get('dias_plazo');
                                                    $formaPagoNombre = $state ? TipoPago::find($state)->nombre : null;
                                                    $fechaCreditoStr = $get('fecha_credito');
                                                    static::calculateCreditValues($valorCredito, $porcentaje, $dias, $formaPagoNombre, $fechaCreditoStr, $set);
                                                }
                                            }),

                                        TextInput::make('dias_plazo')
                                        ->label('Días')
                                        ->numeric()
                                        ->required()
                                        ->default(30) // Valor por defecto: 30 días
                                        ->minValue(1)
                                        ->columnSpanFull()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) use ($isAdicional) {
                                            if (!$isAdicional) {
                                                $valorCredito = (float) $get('valor_credito');
                                                $porcentaje = (float) $get('porcentaje_interes');
                                                $formaPagoId = $get('forma_pago');
                                                $formaPagoNombre = $formaPagoId ? TipoPago::find($formaPagoId)->nombre : null;
                                                $fechaCreditoStr = $get('fecha_credito');
                                                static::calculateCreditValues($valorCredito, $porcentaje, (int) $state, $formaPagoNombre, $fechaCreditoStr, $set);
                                            }
                                        })
                                        ->visible(fn (callable $get) => !$get('es_adicional')),


                                        Select::make('orden_cobro')
                                            ->label('Orden de Cobro')
                                            ->options(OrdenCobro::where('activo', true)->pluck('nombre', 'id_orden_cobro'))
                                            ->default(2) // Asumiendo que 2 es "Último"
                                            ->required()
                                            ->columnSpanFull()





                                    ])
                                    ->columnSpanFull(),
                            ]),

                            // Columna derecha (Resultados calculados)
                            Forms\Components\Group::make([
                                Forms\Components\Section::make('')
                                    ->schema([
                                        // Saldo y Valor de Cuota en una sola fila
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                TextInput::make('saldo_actual')
                                                    ->label('Saldo')
                                                    ->numeric()
                                                    ->extraInputAttributes([
                                                        'class' => 'bg-cyan-100 text-cyan-900',
                                                        'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                                    ])
                                                    ->disabled(),

                                                TextInput::make('valor_cuota')
                                                    ->label('Valor de la Cuota')
                                                    ->numeric()
                                                    ->extraInputAttributes([
                                                        'class' => 'bg-cyan-100 text-cyan-900',
                                                        'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                                    ])
                                                    ->disabled(),
                                            ])
                                            ->visible(fn (callable $get) => !$get('es_adicional')),

                                        // Campo para nombre Yape - Select con opciones existentes y posibilidad de escribir (múltiple)
                                        Select::make('nombre_yape')
                                            ->label('Nombre Yape')
                                            ->required(function (callable $get) {
                                                // Verificar si hay al menos un concepto de tipo 'Yape'
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        return true;
                                                    }
                                                }
                                                return false;
                                            })
                                            ->options(function (callable $get, $livewire) {
                                                $clienteId = $get('id_cliente');
                                                $options = [];

                                                if ($clienteId) {
                                                    // Si estamos editando y ya existe un YapeCliente asociado
                                                    if (isset($livewire->record) && $livewire->record->yapeCliente) {
                                                        $nombreActual = $livewire->record->yapeCliente->nombre;
                                                        $options[$nombreActual] = $nombreActual;
                                                    }

                                                    // Obtener SOLO nombres Yape existentes del mismo cliente SIN id_credito asignado
                                                    $yapesExistentes = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                                                        ->whereNull('id_credito')
                                                        ->whereNotNull('nombre')
                                                        ->where('nombre', '!=', '')
                                                        ->pluck('nombre')
                                                        ->unique()
                                                        ->sort();

                                                    // Solo mostrar el nombre completo del cliente si NO hay nombres Yape registrados
                                                    if ($yapesExistentes->isEmpty() && !isset($livewire->record)) {
                                                        $cliente = \App\Models\Clientes::find($clienteId);
                                                        if ($cliente) {
                                                            $options[$cliente->nombre_completo] = $cliente->nombre_completo;
                                                        }
                                                    }

                                                    foreach ($yapesExistentes as $nombre) {
                                                        $options[$nombre] = $nombre;
                                                    }
                                                }

                                                return $options;
                                            })
                                            ->searchable()
                                            ->allowHtml()
                                           // ->placeholder('Seleccionar nombres existentes o escribir nuevos')
                                            ->helperText('Puede seleccionar múltiples nombres Yape')
                                            ->multiple()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('nombre')
                                                    ->label('Nuevo nombre Yape')
                                                    ->required()
                                                    ->placeholder('Escriba el nuevo nombre para Yape')
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                return $data['nombre'];
                                            })
                                            ->columnSpanFull()
                                            ->extraInputAttributes([
                                                'class' => 'bg-cyan-100 text-cyan-900',
                                                'style' => 'background-color:rgb(114, 237, 241) !important; color:rgb(0, 0, 0) !important;'
                                            ])
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $nombres = is_array($state) ? $state : (empty($state) ? [] : [$state]);
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                $yapeMonto = 0;
                                                foreach ($conceptos as $c) {
                                                    if (($c['tipo_concepto'] ?? '') === 'Yape') {
                                                        $yapeMonto = (float) ($c['monto'] ?? 0);
                                                        break;
                                                    }
                                                }
                                                $set('yape_monto_total', $yapeMonto);
                                                $map = [];
                                                if (count($nombres) === 1) {
                                                    $map[$nombres[0]] = $yapeMonto;
                                                } else {
                                                    $count = count($nombres);
                                                    $base = $count > 0 ? round($yapeMonto / $count, 2) : 0.0;
                                                    $acumulado = 0.0;
                                                    foreach ($nombres as $idx => $n) {
                                                        if ($idx < $count - 1) {
                                                            $map[$n] = $base;
                                                            $acumulado += $base;
                                                        } else {
                                                            // Ajustar el último para que la suma sea exactamente igual al monto Yape
                                                            $map[$n] = round($yapeMonto - $acumulado, 2);
                                                        }
                                                    }
                                                }
                                                $set('distribucion_yape', $map);
                                            })
                                            ->afterStateHydrated(function (callable $get, callable $set, $livewire) {
                                                // Solo preseleccionar durante la creación (no en edición)
                                                if (isset($livewire->record)) {
                                                    return;
                                                }

                                                $clienteId = $get('id_cliente');
                                                if (!$clienteId) {
                                                    return;
                                                }

                                                // Verificar si hay conceptos de tipo Yape
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                $tieneYape = false;
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        $tieneYape = true;
                                                        break;
                                                    }
                                                }

                                                if ($tieneYape) {
                                                    // Buscar YapeCliente registrado sin id_credito
                                                    $yapeCliente = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                                                        ->whereNull('id_credito')
                                                        ->whereNotNull('nombre')
                                                        ->where('nombre', '!=', '')
                                                        ->first();

                                                    if ($yapeCliente) {
                                                        // Si hay YapeCliente registrado, usar su nombre
                                                        $set('nombre_yape', [$yapeCliente->nombre]);
                                                    } else {
                                                        // Si no hay YapeCliente, usar nombre del cliente
                                                        $cliente = \App\Models\Clientes::find($clienteId);
                                                        if ($cliente) {
                                                            $set('nombre_yape', [$cliente->nombre_completo]);
                                                        }
                                                    }
                                                }
                                            })
                                            ->visible(function (callable $get) {
                                                // Solo mostrar si NO es adicional Y hay al menos un concepto de tipo 'Yape'
                                                if ($get('es_adicional')) {
                                                    return false;
                                                }

                                                $conceptos = $get('conceptosCredito') ?? [];
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        return true;
                                                    }
                                                }
                                                return false;
                                            }),

                                        // Distribución de monto para múltiples nombres Yape (KeyValue)
                                        Forms\Components\Hidden::make('yape_monto_total')
                                            ->reactive(),
                                        Forms\Components\KeyValue::make('distribucion_yape')
                                            ->label('Distribución por Nombre Yape')
                                            ->keyLabel('Nombre Yape')
                                            ->valueLabel('Monto')
                                            ->disableAddingRows()
                                            ->disableDeletingRows()
                                            ->disableEditingKeys()
                                            ->reactive()
                                            ->helperText(function (callable $get) {
                                                $yapeMonto = (float) ($get('yape_monto_total') ?? 0);
                                                $dist = $get('distribucion_yape') ?? [];
                                                $suma = 0.0;
                                                if (is_array($dist)) {
                                                    foreach ($dist as $monto) {
                                                        $suma += (float) $monto;
                                                    }
                                                }
                                                return 'Total Yape: S/ ' . number_format($yapeMonto, 2) . ' | Asignado: S/ ' . number_format($suma, 2);
                                            })
                                            ->visible(function (callable $get) {
                                                // Visible solo si hay concepto Yape y más de un nombre seleccionado
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                $tieneYape = false;
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        $tieneYape = true;
                                                        break;
                                                    }
                                                }
                                                if (!$tieneYape) {
                                                    return false;
                                                }
                                                $nombres = $get('es_adicional') ? ($get('nombre_yape_adicional') ?? []) : ($get('nombre_yape') ?? []);
                                                return is_array($nombres) && count($nombres) > 1;
                                            }),

                                        // Campo para nombre Yape - CRÉDITOS ADICIONALES (múltiple)
                                        Select::make('nombre_yape_adicional')
                                            ->label('Nombre Yape')
                                            ->required(function (callable $get) {
                                                // Verificar si hay al menos un concepto de tipo 'Yape'
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        return true;
                                                    }
                                                }
                                                return false;
                                            })
                                            ->options(function (callable $get, $livewire) {
                                                $clienteId = $get('id_cliente');
                                                $options = [];

                                                if ($clienteId) {
                                                    // Si estamos editando y ya existe un YapeCliente asociado
                                                    if (isset($livewire->record) && $livewire->record->yapeCliente) {
                                                        $nombreActual = $livewire->record->yapeCliente->nombre;
                                                        $options[$nombreActual] = $nombreActual;
                                                    }

                                                    // Obtener SOLO nombres Yape existentes del mismo cliente SIN id_credito asignado
                                                    $yapesExistentes = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                                                        ->whereNull('id_credito')
                                                        ->whereNotNull('nombre')
                                                        ->where('nombre', '!=', '')
                                                        ->pluck('nombre')
                                                        ->unique()
                                                        ->sort();

                                                    // Solo mostrar el nombre completo del cliente si NO hay nombres Yape registrados
                                                    if ($yapesExistentes->isEmpty() && !isset($livewire->record)) {
                                                        $cliente = \App\Models\Clientes::find($clienteId);
                                                        if ($cliente) {
                                                            $options[$cliente->nombre_completo] = $cliente->nombre_completo;
                                                        }
                                                    }

                                                    foreach ($yapesExistentes as $nombre) {
                                                        $options[$nombre] = $nombre;
                                                    }
                                                }

                                                return $options;
                                            })
                                            ->searchable()
                                            ->allowHtml()
                                            ->placeholder('Seleccionar nombres existentes o escribir nuevos')
                                            ->helperText('Puede seleccionar múltiples nombres Yape')
                                            ->multiple()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('nombre')
                                                    ->label('Nuevo nombre Yape')
                                                    ->required()
                                                    ->placeholder('Escriba el nuevo nombre para Yape')
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                return $data['nombre'];
                                            })
                                            ->columnSpanFull()
                                            ->extraInputAttributes([
                                                'class' => 'bg-cyan-100 text-cyan-900',
                                                'style' => 'background-color:rgb(114, 237, 241) !important; color:rgb(0, 0, 0) !important;'
                                            ])
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $nombres = is_array($state) ? $state : (empty($state) ? [] : [$state]);
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                $yapeMonto = 0;
                                                foreach ($conceptos as $c) {
                                                    if (($c['tipo_concepto'] ?? '') === 'Yape') {
                                                        $yapeMonto = (float) ($c['monto'] ?? 0);
                                                        break;
                                                    }
                                                }
                                                $set('yape_monto_total', $yapeMonto);
                                                $map = [];
                                                if (count($nombres) === 1) {
                                                    $map[$nombres[0]] = $yapeMonto;
                                                } else {
                                                    $count = count($nombres);
                                                    $base = $count > 0 ? round($yapeMonto / $count, 2) : 0.0;
                                                    $acumulado = 0.0;
                                                    foreach ($nombres as $idx => $n) {
                                                        if ($idx < $count - 1) {
                                                            $map[$n] = $base;
                                                            $acumulado += $base;
                                                        } else {
                                                            $map[$n] = round($yapeMonto - $acumulado, 2);
                                                        }
                                                    }
                                                }
                                                $set('distribucion_yape', $map);
                                            })
                                            ->afterStateHydrated(function (callable $get, callable $set, $livewire) {
                                                // Solo preseleccionar durante la creación (no en edición)
                                                if (isset($livewire->record)) {
                                                    return;
                                                }

                                                $clienteId = $get('id_cliente');
                                                if (!$clienteId) {
                                                    return;
                                                }

                                                // Verificar si hay conceptos de tipo Yape
                                                $conceptos = $get('conceptosCredito') ?? [];
                                                $tieneYape = false;
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        $tieneYape = true;
                                                        break;
                                                    }
                                                }

                                                if ($tieneYape) {
                                                    // Buscar YapeCliente registrado sin id_credito
                                                    $yapeCliente = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                                                        ->whereNull('id_credito')
                                                        ->whereNotNull('nombre')
                                                        ->where('nombre', '!=', '')
                                                        ->first();

                                                    if ($yapeCliente) {
                                                        // Si hay YapeCliente registrado, usar su nombre
                                                        $set('nombre_yape_adicional', [$yapeCliente->nombre]);
                                                    } else {
                                                        // Si no hay YapeCliente, usar nombre del cliente
                                                        $cliente = \App\Models\Clientes::find($clienteId);
                                                        if ($cliente) {
                                                            $set('nombre_yape_adicional', [$cliente->nombre_completo]);
                                                        }
                                                    }
                                                }
                                            })
                                            ->visible(function (callable $get) {
                                                // Solo mostrar si ES adicional Y hay al menos un concepto de tipo 'Yape'
                                                if (!$get('es_adicional')) {
                                                    return false;
                                                }

                                                $conceptos = $get('conceptosCredito') ?? [];
                                                foreach ($conceptos as $concepto) {
                                                    if (($concepto['tipo_concepto'] ?? '') === 'Yape') {
                                                        return true;
                                                    }
                                                }
                                                return false;
                                            }),

                                        TextInput::make('numero_cuotas')
                                            ->label('No. de Cuotas')
                                            ->numeric()
                                            ->disabled()
                                            ->columnSpanFull()
                                            ->extraInputAttributes([
                                                'class' => 'bg-cyan-100 text-cyan-900',
                                                'style' => 'background-color:rgb(201, 201, 201) !important; color:rgb(0, 0, 0) !important;'
                                            ])
                                            ->visible(fn (callable $get) => !$get('es_adicional')),

                                        DatePicker::make('fecha_vencimiento')
                                            ->label('Fecha de Vencimiento')
                                            ->disabled()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'h-[24px]'])
                                            ->visible(fn (callable $get) => !$get('es_adicional')),

                                        DatePicker::make('fecha_proximo_pago')
                                            ->label('Fecha de Próximo Pago')
                                            ->disabled()
                                            ->displayFormat('d/m/Y')
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'h-[24px]'])
                                            ->visible(fn (callable $get) => !$get('es_adicional')),

                                        Forms\Components\Repeater::make('conceptosCredito')
                                            ->label('Desglose del Crédito')
                                           // ->relationship() // esto asume que tu modelo tiene ->conceptosCredito()
                                            ->schema([
                                                Select::make('tipo_concepto')
                                                    ->label('Forma de entrega')
                                                    ->options([
                                                        'Efectivo' => 'Efectivo',
                                                        'Yape' => 'Yape',
                                                        'Caja' => 'Caja',
                                                        'Caja Dueño' => 'Caja Dueño',
                                                        'Saldo renovación' => 'Saldo renovación',
                                                        'Abono para completar préstamo' => 'Abono para completar préstamo',
                                                    ])
                                                    ->required()
                                                    ->reactive() // Para mostrar/ocultar foto_comprobante según valor
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire) {
                                                         if ($state === 'Yape') {
                                                             $clienteId = $get('../../id_cliente');
                                                             if ($clienteId) {
                                                                 // Buscar YapeCliente registrado sin id_credito
                                                                 $yapeCliente = \App\Models\YapeCliente::where('id_cliente', $clienteId)
                                                                     ->whereNull('id_credito')
                                                                     ->whereNotNull('nombre')
                                                                     ->where('nombre', '!=', '')
                                                                     ->first();

                                                                 if ($yapeCliente) {
                                                                     // Si hay YapeCliente registrado, usar su nombre y monto
                                                                     $set('../../nombre_yape', [$yapeCliente->nombre]);
                                                                     $set('monto', $yapeCliente->monto);
                                                                 } else {
                                                                     // Si no hay YapeCliente, usar nombre del cliente
                                                                     $cliente = \App\Models\Clientes::find($clienteId);
                                                                     if ($cliente) {
                                                                         $set('../../nombre_yape', [$cliente->nombre_completo]);
                                                                     }
                                                                 }
                                                             }
                                                         }
                                                     }),

                                                TextInput::make('monto')
                                                    ->label('Monto')
                                                    ->numeric()
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire) {
                                                        // Solo procesar si hay un monto válido
                                                        if (!$state || !is_numeric($state)) {
                                                            return;
                                                        }

                                                        // Si este concepto es Yape, recalcular la distribución por Nombre Yape
                                                        if ($get('tipo_concepto') === 'Yape') {
                                                            // Actualizar el monto total Yape para refrescar helperText
                                                            $set('../../yape_monto_total', (float) $state);
                                                            $nombres = $get('../../es_adicional') ? ($get('../../nombre_yape_adicional') ?? []) : ($get('../../nombre_yape') ?? []);
                                                            if (is_string($nombres)) {
                                                                $nombres = [$nombres];
                                                            }
                                                            $map = [];
                                                            if (is_array($nombres) && count($nombres) > 0) {
                                                                if (count($nombres) === 1) {
                                                                    $map[$nombres[0]] = (float) $state;
                                                                } else {
                                                                    $count = count($nombres);
                                                                    $base = $count > 0 ? round(((float) $state) / $count, 2) : 0.0;
                                                                    $acumulado = 0.0;
                                                                    foreach ($nombres as $idx => $n) {
                                                                        if ($idx < $count - 1) {
                                                                            $map[$n] = $base;
                                                                            $acumulado += $base;
                                                                        } else {
                                                                            $map[$n] = round(((float) $state) - $acumulado, 2);
                                                                        }
                                                                    }
                                                                }
                                                                $set('../../distribucion_yape', $map);
                                                            }
                                                        }

                                                        $valorCredito = (float) ($get('../../valor_credito') ?? 0);
                                                        $conceptos = $get('../../conceptosCredito') ?? [];

                                                        $sumaTotal = 0;
                                                        foreach ($conceptos as $concepto) {
                                                            if (isset($concepto['monto']) && is_numeric($concepto['monto'])) {
                                                                $sumaTotal += (float) $concepto['monto'];
                                                            }
                                                        }

                                                        // Solo mostrar notificación si hay diferencia significativa (falta dinero)
                                                        $diferencia = $valorCredito - $sumaTotal;

                                                        /*
                                                        if ($diferencia > 0.01) { // Falta dinero
                                                            \Filament\Notifications\Notification::make()
                                                                ->id('credito-suma-info')
                                                                ->title('Información')
                                                                ->body("Falta S/ " . number_format($diferencia, 2) . " para completar el valor del crédito")
                                                                ->warning()
                                                                ->send();
                                                        } elseif ($diferencia < -0.01) { // Excede el valor
                                                            \Filament\Notifications\Notification::make()
                                                                ->id('credito-suma-exceso')
                                                                ->title('Información')
                                                                ->body("La suma excede el valor del crédito por S/ " . number_format(abs($diferencia), 2))
                                                                ->warning()
                                                                ->send();
                                                        }
                                                                */
                                                        // No mostrar notificación cuando coincide exactamente para evitar spam

                                                        // ELIMINAR ESTE BLOQUE COMPLETO - CAUSA DUPLICACIÓN
                                                        // if ($get('tipo_concepto') === 'Yape' && $state) {
                                                        //     $nombreYape = $livewire->data['nombre_yape'] ?? null;
                                                        //     $clienteId = $livewire->data['id_cliente'] ?? null;
                                                        //
                                                        //     if ($nombreYape && $clienteId) {
                                                        //         // Crear o actualizar registro en yape_clientes
                                                        //         \App\Models\YapeCliente::updateOrCreate(
                                                        //             [
                                                        //                 'id_cliente' => $clienteId,
                                                        //                 'nombre' => $nombreYape,
                                                        //             ],
                                                        //             [
                                                        //                 'monto' => $state,
                                                        //                 'entregar' => $state,
                                                        //                 'user_id' => auth()->id(),
                                                        //             ]
                                                        //         );
                                                        //     }
                                                        // }
                                                    }),


                                                FileUpload::make('foto_comprobante')
                                                    ->label(fn ($get) => match ($get('tipo_concepto')) {
                                                        'Yape' => 'Comprobante Yape',
                                                        'Efectivo' => 'Comprobante Efectivo',
                                                        default => 'Comprobante'
                                                    })
                                                    ->directory(fn ($get) => match ($get('tipo_concepto')) {
                                                        'Yape' => 'comprobantes/yape',
                                                        'Efectivo' => 'comprobantes/efectivo',
                                                        default => 'comprobantes/generales'
                                                    })
                                                    ->visible(fn ($get) => in_array($get('tipo_concepto'), ['Yape', 'Efectivo']))
                                                    ->image()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                                    //->maxSize(2048) // 2MB máximo
                                                    ->columnSpanFull()
                                                    // ->afterStateUpdated(function (TemporaryUploadedFile $state, $get) {
                                                    //     $directory = match ($get('tipo_concepto')) {
                                                    //         'Yape' => 'comprobantes/yape',
                                                    //         'Efectivo' => 'comprobantes/efectivo',
                                                    //         default => 'comprobantes/generales'
                                                    //     };

                                                    //     $image = Image::make($state->getRealPath())
                                                    //         ->resize(800, null, function ($constraint) {
                                                    //             $constraint->aspectRatio();
                                                    //         })
                                                    //         ->encode('jpg', 70); // 70% de calidad

                                                    //     Storage::disk('public')->put(
                                                    //         $directory . '/' . $state->getFilename(),
                                                    //         $image->stream()
                                                    //     );
                                                    // }),
                                            ])
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->createItemButtonLabel('Agregar concepto')
                                            ->columns(2),
                                    ])
                                    ->columnSpanFull(),
                            ])
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('fecha_credito')
                    ->label('Fecha Crédito')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dias_transcurridos')
                    ->label('Días')
                    ->getStateUsing(function ($record) {
                        return $record->fecha_credito ? $record->fecha_credito->diffInDays(now()) : null;
                    })
                    ->color(function ($record) {
                        return now()->gt($record->fecha_vencimiento) ? 'danger' : null;
                    })
                    ->weight(function ($record) {
                        return now()->gt($record->fecha_vencimiento) ? 'bold' : null;
                    }),

               Tables\Columns\TextColumn::make('usuarioCreador.name')
                    ->label('Creado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(function ($livewire = null) {
                        // Si hay un cliente seleccionado desde el header, siempre mostrar
                        if (!$livewire) {
                            $livewire = $this;
                        }
                        if ($livewire && property_exists($livewire, 'clienteId') && $livewire->clienteId) {
                            return true;
                        }
                        // Si no hay cliente seleccionado, usar configuración de general settings
                        return app(\App\Settings\GeneralSettings::class)->mostrar_usuario_creador ?? false;
                    }),

                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(['cliente.nombre', 'cliente.apellido'])
                    ->sortable()
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn ($record) => $record->cliente ? route('filament.resources.creditos.historial-cliente', ['cliente' => $record->cliente->id_cliente]) : null)
                    ->openUrlInNewTab(false)
                    ->tooltip('Ver historial del cliente')
                    ->visible(function ($livewire = null) {
                        // Ocultar la columna cliente cuando hay un cliente seleccionado
                        if (!$livewire) {
                            $livewire = $this;
                        }
                        return !($livewire && property_exists($livewire, 'clienteId') && $livewire->clienteId);
                    }),

                Tables\Columns\TextColumn::make('valor_credito')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('porcentaje_interes')
                    ->label(function ($record) {
                        return ($record && $record->es_adicional) ? 'Cuota Diaria' : 'Interés';
                    })
                    ->formatStateUsing(function ($state, $record) {
                        return ($record && $record->es_adicional) ? '0' : $state;
                    })
                    ->suffix(function ($record) {
                        return ($record && $record->es_adicional) ? null : '%';
                    })
                    ->sortable()
                    ->visible(function ($livewire = null) {
                        // Si hay un cliente seleccionado desde el header, siempre mostrar
                        if (!$livewire) {
                            $livewire = $this;
                        }
                        if ($livewire && property_exists($livewire, 'clienteId') && $livewire->clienteId) {
                            return true;
                        }
                        // Si no hay cliente seleccionado, usar configuración de general settings
                        return app(\App\Settings\GeneralSettings::class)->mostrar_porcentaje_interes ?? true;
                    }),

                Tables\Columns\TextColumn::make('tipoPago.nombre')
                    ->label('Tipo')
                    ->sortable()
                    ->visible(function ($livewire = null) {
                        // Si hay un cliente seleccionado desde el header, siempre mostrar
                        if (!$livewire) {
                            $livewire = $this;
                        }
                        if ($livewire && property_exists($livewire, 'clienteId') && $livewire->clienteId) {
                            return true;
                        }
                        // Si no hay cliente seleccionado, usar configuración de general settings
                        return app(\App\Settings\GeneralSettings::class)->mostrar_tipo_pago ?? true;
                    }),

                Tables\Columns\TextColumn::make('numero_cuotas')
                    ->label('Nr. cuotas')
                    ->sortable()
                    ->visible(function ($livewire = null) {
                        // Si hay un cliente seleccionado desde el header, siempre mostrar
                        if (!$livewire) {
                            $livewire = $this;
                        }
                        if ($livewire && property_exists($livewire, 'clienteId') && $livewire->clienteId) {
                            return true;
                        }
                        // Si no hay cliente seleccionado, usar configuración de general settings
                        return app(\App\Settings\GeneralSettings::class)->mostrar_numero_cuotas ?? true;
                    }),

                Tables\Columns\TextColumn::make('valor_cuota')
                    ->label('Cuota')
                    ->formatStateUsing(function ($state, $record) {
                        return ($record && $record->es_adicional) ? $record->porcentaje_interes : $state;
                    })
                    ->prefix(function ($record) {
                        return ($record && $record->es_adicional) ? 'S/' : null;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo_actual')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        return now()->gt($record->fecha_vencimiento) ? 'danger' : null;
                    })
                    ->weight(function ($record) {
                        return now()->gt($record->fecha_vencimiento) ? 'bold' : null;
                    }),


                Tables\Columns\TextColumn::make('conceptosCredito')
                    ->label('Detalle Entrega')
                    ->formatStateUsing(function ($record) {
                        return $record->conceptosCredito
                            ->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))
                            ->join(' | ');
                    })
                    ->wrap() // para que no se desborde si es muy largo
                    ->searchable(false),

                /*
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                ->label('último Pago')
                ->date('d/m/Y')
                ->sortable(),
                */

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => $record->saldo_actual > 0 ? 'Activo' : 'Pagado')
                    ->colors([
                        'success' => 'Activo',
                        'danger' => 'Pagado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('forma_pago')
                    ->label('Forma de Pago')
                    ->relationship('tipoPago', 'nombre'),

                Tables\Filters\SelectFilter::make('orden_cobro')
                    ->label('Orden de Cobro')
                    ->relationship('ordenCobro', 'nombre'),

                // Removido el filtro de activos que aparecía en la URL
                // Tables\Filters\Filter::make('activos')
                //     ->label('Solo créditos activos')
                //     ->query(fn($query) => $query->where('saldo_actual', '>', 0)),
            ])
            ->actions([

                Tables\Actions\Action::make('view_abonos_history')
                    ->label('')
                    ->icon('heroicon-o-document-text') // Puedes usar 'heroicon-o-eye' o 'heroicon-o-document-text'
                    ->url(fn(Creditos $record): string => CreditosResource::getUrl('view', ['record' => $record->id_credito]))
                    ->color('primary')
                    ->tooltip('Ver Historial de Abonos')
                    ->button(), // Display as a button

                Tables\Actions\Action::make('view_comprobantes')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color(fn($record) => $record->conceptosCredito->where('foto_comprobante', '!=', null)->isNotEmpty() ? 'primary' : 'secondary')
                    ->size('sm')
                    ->button()
                    ->modalHeading('Comprobantes de Pago')
                    ->form(function ($record) {
                        $comprobantes = $record->conceptosCredito->where('foto_comprobante', '!=', null);

                        $components = [];

                        if ($comprobantes->isNotEmpty()) {
                            foreach ($comprobantes as $comprobante) {
                                $imageUrl = asset('storage/' . $comprobante->foto_comprobante);
                                $tipo = $comprobante->tipo_concepto;

                                $imageHtml = <<<HTML
                                    <div class="space-y-1 p-2">
                                        <p class="text-xs font-medium text-gray-500">Comprobante {$tipo}</p>
                                        <div class="flex justify-center">
                                            <img src="$imageUrl"
                                                class="rounded-lg max-h-[290px] max-w-full object-contain cursor-pointer"
                                                onclick="window.open(this.src, '_blank')">
                                        </div>
                                    </div>
                                HTML;

                                $components[] = \Filament\Forms\Components\Card::make()
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('comprobante_' . $comprobante->id)
                                            ->content(new \Illuminate\Support\HtmlString($imageHtml))
                                            ->disableLabel()
                                    ]);
                            }
                        } else {
                            $components[] = \Filament\Forms\Components\Placeholder::make('no_comprobantes')
                                ->content('No hay comprobantes disponibles')
                                ->disableLabel();
                        }

                        return $components;
                    })
                    ->modalWidth('xl')
                    ->modalButton('Cerrar')
                    ->hidden(fn($record) => $record->conceptosCredito->where('foto_comprobante', '!=', null)->isEmpty())
                    ->extraAttributes([
                        'title' => 'Ver Comprobantes',
                        'class' => 'hover:bg-success-50 rounded-full'
                    ])
                    ->action(function () {
                        // Acción vacía necesaria para el modal
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->size('sm')
                    ->button()
                    ->tooltip('Eliminar Crédito')
                    ->before(function ($record) {
                        // Verificar que el crédito no tenga abonos
                        if ($record->abonos()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('No se puede eliminar el crédito')
                                ->body('Este crédito tiene abonos realizados y no puede ser eliminado.')
                                ->danger()
                                ->send();
                            throw new \Exception('El crédito tiene abonos realizados.');
                        }

                        // Eliminar el YapeCliente asociado si existe
                        if ($record->yapeCliente) {
                            $record->yapeCliente->forceDelete();
                        }

                        $clienteNombre = $record->cliente?->nombre . ' ' . $record->cliente?->apellido;
                        $rutaNombre = $record->cliente?->ruta?->nombre ?? 'Ruta desconocida';

                        \App\Models\LogActividad::registrar(
                            'Créditos',
                            "Eliminó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
                            [
                                'credito_id' => $record->id_credito,
                                'cliente_id' => $record->id_cliente,
                                'datos_eliminados' => $record->toArray(),
                            ]
                        );
                    })
                    ->after(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Crédito eliminado exitosamente')
                            ->success()
                            ->send();
                    })
                    ->extraAttributes([
                        'title' => 'Eliminar',
                        'class' => 'hover:bg-danger-50 rounded-full'
                    ])
            ])

            ->bulkActions([]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditos::route('/'),
            'create' => Pages\CreateCreditos::route('/create'),
            'edit' => Pages\EditCreditos::route('/{record}/edit'),
            'view' => Pages\ViewCredito::route('/{record}'), // Ensure this is active
            'historial-cliente' => Pages\ViewHistorialCliente::route('/historial-cliente/{cliente}'),
            'historial-credito' => Pages\ViewHistorialCliente::route('/historial-credito/{credito}'),

        ];
    }
}
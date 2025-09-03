<?php

namespace App\Filament\Pages;

use App\Models\Clientes;
use App\Models\Ruta;
use App\Models\Creditos;
use App\Models\LogActividad;
use App\Models\Concepto;
use App\Models\TipoPago;
use App\Models\OrdenCobro;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TrasladarClientes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-switch-horizontal';
    protected static ?string $navigationLabel = 'Trasladar Clientes';
    protected static ?string $title = 'Trasladar Clientes';
    protected static ?string $slug = 'trasladar-clientes';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Movimientos';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.trasladar-clientes';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('Listar Trasladar Clientes');
    }

    public $rutaOrigen = null;
    public $rutaDestino = null;
    public $tipoTraslado = 'solo_saldo';
    public $clientesSeleccionados = [];
    public $clientesDisponibles = [];
    public $clientesConDatos = [];

     public function mount(): void
    {
        $this->clientesSeleccionados = [];
        $this->clientesDisponibles = [];
        $this->clientesConDatos = [];
        $this->tipoTraslado = 'solo_saldo';

        // Obtener la ruta de la sesión como ruta de origen por defecto
        $rutaSesion = Session::get('selected_ruta_id');
        $this->rutaOrigen = $rutaSesion;
        $this->rutaDestino = null;

        $this->form->fill([
            'rutaOrigen' => $rutaSesion,
            'rutaDestino' => null,
            'tipoTraslado' => 'solo_saldo',
            'clientesSeleccionados' => [],
        ]);

        if ($this->rutaOrigen) {
            $this->cargarClientesDisponibles();
        }

    }

    public function getClientesDisponiblesProperty(): array
    {
        return is_array($this->clientesDisponibles) ? $this->clientesDisponibles : [];
    }

    public function getClientesSeleccionadosProperty(): array
    {
        return is_array($this->clientesSeleccionados) ? $this->clientesSeleccionados : [];
    }

    public function getClientesConDatosProperty(): array
    {
        return is_array($this->clientesConDatos) ? $this->clientesConDatos : [];
    }

    public function getRutaOrigenProperty()
    {
        return $this->rutaOrigen;
    }

    public function getRutaDestinoProperty()
    {
        return $this->rutaDestino;
    }

    public function getTipoTrasladoProperty(): string
    {
        return is_string($this->tipoTraslado) ? $this->tipoTraslado : 'solo_saldo';
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('rutaOrigen')
                                ->label('De la Ruta')
                                ->options(function () {
                                    try {
                                        $rutas = Ruta::where('activa', true)->get();
                                        $options = [];
                                        foreach ($rutas as $ruta) {
                                            // Construir el nombre de forma segura
                                            $codigo = $ruta->codigo ?? '';
                                            $nombre = $ruta->nombre ?? '';
                                            $nombreCompleto = trim($codigo . ' - ' . $nombre);
                                            if (empty($nombreCompleto) || $nombreCompleto === ' - ') {
                                                $nombreCompleto = 'Ruta ' . $ruta->id_ruta;
                                            }
                                            $options[$ruta->id_ruta] = $nombreCompleto;
                                        }
                                        return $options;
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->placeholder('Seleccione ruta de origen')
                                ->reactive()
                                ->afterStateUpdated(function ($state) {
                                    $this->rutaOrigen = $state;
                                    $this->cargarClientesDisponibles();
                                    $this->clientesSeleccionados = [];
                                })
                                ->required(),

                            Select::make('rutaDestino')
                                ->label('A la Ruta')
                                ->options(function () {
                                    try {
                                        $rutas = Ruta::where('activa', true);
                                        if ($this->rutaOrigen) {
                                            $rutas = $rutas->where('id_ruta', '!=', $this->rutaOrigen);
                                        }
                                        $rutas = $rutas->get();
                                        $options = [];
                                        foreach ($rutas as $ruta) {
                                            // Construir el nombre de forma segura
                                            $codigo = $ruta->codigo ?? '';
                                            $nombre = $ruta->nombre ?? '';
                                            $nombreCompleto = trim($codigo . ' - ' . $nombre);
                                            if (empty($nombreCompleto) || $nombreCompleto === ' - ') {
                                                $nombreCompleto = 'Ruta ' . $ruta->id_ruta;
                                            }
                                            $options[$ruta->id_ruta] = $nombreCompleto;
                                        }
                                        return $options;
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->placeholder('Seleccione ruta destino')
                                ->reactive()
                                ->afterStateUpdated(function ($state) {
                                    $this->rutaDestino = $state;
                                })
                                ->required(),
                        ]),

                    Radio::make('tipoTraslado')
                        ->label('Tipo de Traslado')
                        ->options([
                            'solo_saldo' => 'Trasladar solo el saldo',
                            'historial_completo' => 'Trasladar historial completo',
                        ])
                        ->default('solo_saldo')
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->tipoTraslado = is_string($state) ? $state : 'solo_saldo';
                        }),
                ])
        ];
    }

    public function cargarClientesDisponibles(): void
    {
        if (!$this->rutaOrigen) {
            $this->clientesDisponibles = [];
            $this->clientesConDatos = [];
            return;
        }

        try {
            $clientes = Clientes::where('id_ruta', $this->rutaOrigen)
                ->where('activo', true)
                ->with(['creditos' => function($query) {
                    $query->where('saldo_actual', '>', 0)
                          ->orderBy('fecha_credito', 'desc');
                }])
                ->get();

            $options = [];
            $clientesConDatos = [];

            foreach ($clientes as $cliente) {
                $nombre = trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido ?? ''));
                if (empty($nombre)) {
                    $nombre = 'Cliente ' . $cliente->id_cliente;
                }
                $options[$cliente->id_cliente] = $nombre;

                // Obtener el crédito más reciente con saldo
                $creditoActivo = $cliente->creditos->first();

                $clientesConDatos[] = [
                    'id_cliente' => $cliente->id_cliente,
                    'nombre' => $nombre,
                    'fecha_credito' => $creditoActivo ? $creditoActivo->fecha_credito->format('d/M/y') : '-',
                    'fecha_vencimiento' => $creditoActivo ? $creditoActivo->fecha_vencimiento->format('d/M/y') : '-',
                    'fecha_proximo_pago' => $creditoActivo ? $creditoActivo->fecha_proximo_pago->format('d/M/y') : '-',
                    'saldo' => $creditoActivo ? 'S/' . number_format($creditoActivo->saldo_actual, 2) : 'S/0.00',
                    'saldo_numerico' => $creditoActivo ? $creditoActivo->saldo_actual : 0,
                ];
            }

            $this->clientesDisponibles = $options;
            $this->clientesConDatos = $clientesConDatos;
        } catch (\Exception $e) {
            $this->clientesDisponibles = [];
            $this->clientesConDatos = [];
            Notification::make()
                ->title('Error')
                ->body('Error al cargar clientes')
                ->danger()
                ->send();
        }
    }

    public function toggleClienteSeleccion($clienteId): void
    {
        if (in_array($clienteId, $this->clientesSeleccionados)) {
            $this->clientesSeleccionados = array_values(array_diff($this->clientesSeleccionados, [$clienteId]));
        } else {
            $this->clientesSeleccionados[] = $clienteId;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('trasladar')
                ->label('Trasladar')
                ->action('trasladar')
                ->color('primary')
                ->disabled(fn () => empty($this->clientesSeleccionados))
        ];
    }

    public function trasladar(): void
    {
        if (!is_array($this->clientesSeleccionados) || empty($this->clientesSeleccionados)) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar al menos un cliente')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $clientesTrasladados = 0;
            $clientesInfo = []; // Para almacenar información de los clientes trasladados

            // Obtener información de las rutas para el log
            $rutaOrigen = Ruta::find($this->rutaOrigen);
            $rutaDestino = Ruta::find($this->rutaDestino);

            foreach ($this->clientesSeleccionados as $clienteId) {
                $cliente = Clientes::find($clienteId);
                if ($cliente) {
                    // Guardar información del cliente para el log
                    $clientesInfo[] = [
                        'id' => $cliente->id_cliente,
                        'nombre' => trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido ?? '')),
                        'ruta_origen_id' => $this->rutaOrigen,
                        'ruta_destino_id' => $this->rutaDestino
                    ];

                    $cliente->update(['id_ruta' => $this->rutaDestino]);
                    $clientesTrasladados++;
                }
            }

            DB::commit();

            // Registrar el log de actividad después del traslado exitoso
            $nombreRutaOrigen = $rutaOrigen ? ($rutaOrigen->codigo . ' - ' . $rutaOrigen->nombre) : 'Ruta desconocida';
            $nombreRutaDestino = $rutaDestino ? ($rutaDestino->codigo . ' - ' . $rutaDestino->nombre) : 'Ruta desconocida';

            $nombresClientes = array_map(function($cliente) {
                return $cliente['nombre'];
            }, $clientesInfo);

            $tipoTrasladoTexto = $this->tipoTraslado === 'solo_saldo' ? 'solo saldo' : 'historial completo';

            LogActividad::registrar(
                'Traslados',
                "Trasladó {$clientesTrasladados} cliente(s) de la ruta {$nombreRutaOrigen} a la ruta {$nombreRutaDestino} ({$tipoTrasladoTexto}): " . implode(', ', $nombresClientes),
                [
                    'ruta_origen_id' => $this->rutaOrigen,
                    'ruta_destino_id' => $this->rutaDestino,
                    'tipo_traslado' => $this->tipoTraslado,
                    'cantidad_clientes' => $clientesTrasladados,
                    'clientes_trasladados' => $clientesInfo,
                    'ruta_origen_nombre' => $nombreRutaOrigen,
                    'ruta_destino_nombre' => $nombreRutaDestino,
                ]
            );

            Notification::make()
                ->title('Traslado Exitoso')
                ->body("Se trasladaron {$clientesTrasladados} cliente(s) exitosamente.")
                ->success()
                ->send();

            // Obtener la ruta de la sesión para volver a seleccionarla
            $rutaSesion = Session::get('selected_ruta_id');

            // Limpiar solo los datos necesarios pero mantener la ruta de origen de la sesión
            $this->clientesSeleccionados = [];
            $this->clientesDisponibles = [];
            $this->clientesConDatos = [];
            $this->rutaDestino = null;
            $this->rutaOrigen = $rutaSesion;
            $this->tipoTraslado = 'solo_saldo';

            // Llenar el formulario con la ruta de la sesión preseleccionada
            $this->form->fill([
                'rutaOrigen' => $rutaSesion,
                'rutaDestino' => null,
                'tipoTraslado' => 'solo_saldo',
                'clientesSeleccionados' => [],
            ]);

            // Si hay una ruta de origen, cargar los clientes automáticamente
            if ($this->rutaOrigen) {
                $this->cargarClientesDisponibles();
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error en el Traslado')
                ->body('Ocurrió un error al trasladar los clientes')
                ->danger()
                ->send();
        }
    }
}

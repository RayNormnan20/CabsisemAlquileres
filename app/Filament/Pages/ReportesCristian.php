<?php

namespace App\Filament\Pages;

use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\ConceptoAbono;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\Alquiler;
use App\Models\PagoAlquiler;
use App\Models\Ruta;
use App\Helpers\RutaPermissionHelper;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Livewire\Traits\RouteValidation;

class ReportesCristian extends Page implements HasForms
{
    use InteractsWithForms;
    use RouteValidation;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes Cristian';
    protected static ?string $title = 'Panel de Reportes';
    protected static ?string $slug = 'reportes-cristian';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Movimientos';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.reportes-cristian';

    public static function canAccess(): bool
    {
        // Usar el nuevo sistema: permisos normales O estar en ruta asignada
        return RutaPermissionHelper::canAccessModule('ReportesCristian', 'Ver Reportes Cristian');
    }

    public $fechaInicio = null;
    public $fechaFin = null;
    public $rutaSeleccionada = null;
    // Propiedades para filtro de fechas
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy';
    public bool $fechasValidas = true;

    // Datos de los reportes
    public $cantidadAbonado = 0;
    public $totalYapeado = 0;
    public $totalEfectivo = 0;
    public $totalTransferencia = 0;
    public $clientesAtendidos = 0;
    public $clientesPendientes = 0;
    public $datosCreditos = [];
    public $datosAbonos = [];
    public $conceptosSinAbono = []; // NUEVO: Conceptos sin id_abono
    public $records = [];
    public $creditosRecords = [];
    // Deuda de alquiler por edificio
    public $deudaAlquilerPorEdificio = [];

    // Modal de configuración de rutas
    public $mostrarModalRutas = false;
    public $rutasSeleccionadas = [];

    /**
     * Verificar si el usuario es administrador o super administrador
     */
    public function esAdministrador(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('Administrador') || $user->hasRole('Super Administrador') || $user->hasRole('Administrador 2'));
    }

    /**
     * Verificar si el acceso es mediante asignación de rutas (no administrador)
     */
    public function esAccesoPorRuta(): bool
    {
        return !$this->esAdministrador() && RutaPermissionHelper::isUserInAssignedRoute();
    }

    public function mount()
    {
        // Validar y corregir la ruta seleccionada usando el trait
        $this->validateAndCorrectSelectedRoute();
        $this->fechaInicio = Carbon::today()->format('Y-m-d');
        $this->fechaFin = Carbon::today()->format('Y-m-d');
        $this->rutaSeleccionada = 'todas'; // Establecer valor por defecto
        $this->inicializarFiltroFecha();
        $this->cargarDatos();
        $this->cargarRutasSeleccionadas();
    }

    public function abrirModalRutas()
    {
        $this->mostrarModalRutas = true;
    }

    public function cerrarModalRutas()
    {
        $this->mostrarModalRutas = false;
    }

    public function cargarRutasSeleccionadas()
    {
        // Cargar las rutas que pueden ver ReportesCristian desde la configuración
        $configuracion = DB::table('configuracion_rutas_reportes')
            ->where('modulo', 'ReportesCristian')
            ->first();

        if ($configuracion) {
            $this->rutasSeleccionadas = json_decode($configuracion->rutas_permitidas, true) ?? [];
        } else {
            // Si no hay configuración, todas las rutas pueden ver por defecto
            // $this->rutasSeleccionadas = \App\Models\Ruta::where('activa', true)->pluck('nombre')->toArray(); // Comentado temporalmente - quizás se use más adelante
        }
    }

    public function guardarConfiguracionRutas()
    {
        try {
            // Guardar o actualizar la configuración en la base de datos
            DB::table('configuracion_rutas_reportes')->updateOrInsert(
                ['modulo' => 'ReportesCristian'],
                [
                    'rutas_permitidas' => json_encode($this->rutasSeleccionadas),
                    'updated_at' => now()
                ]
            );

            $this->mostrarModalRutas = false;

            Notification::make()
                ->title('Configuración guardada')
                ->body('Las rutas autorizadas para ver ReportesCristian han sido actualizadas.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar')
                ->body('No se pudo guardar la configuración: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function inicializarFiltroFecha()
    {
        $this->fechaDesde = Carbon::today()->format('Y-m-d');
        $this->fechaHasta = Carbon::today()->format('Y-m-d');
        $this->periodoSeleccionado = 'hoy';
        $this->actualizarFechasPorPeriodo();
    }

    public function updatedPeriodoSeleccionado()
    {
        $this->actualizarFechasPorPeriodo();
        $this->cargarDatos();
    }

    public function updatedFechaDesde()
    {
        $this->validarFechas();
        if ($this->fechasValidas) {
            $this->fechaInicio = $this->fechaDesde;
            $this->cargarDatos();
        }
    }

    public function updatedFechaHasta()
    {
        $this->validarFechas();
        if ($this->fechasValidas) {
            $this->fechaFin = $this->fechaHasta;
            $this->cargarDatos();
        }
    }

    public function actualizarFechasPorPeriodo()
    {
        $hoy = Carbon::today();

        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = $hoy->format('Y-m-d');
                $this->fechaHasta = $hoy->format('Y-m-d');
                break;
            case 'ayer':
                $ayer = $hoy->copy()->subDay();
                $this->fechaDesde = $ayer->format('Y-m-d');
                $this->fechaHasta = $ayer->format('Y-m-d');
                break;
            case 'esta_semana':
                $this->fechaDesde = $hoy->copy()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'semana_pasada':
                $this->fechaDesde = $hoy->copy()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'este_mes':
                $this->fechaDesde = $hoy->copy()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_pasado':
                $this->fechaDesde = $hoy->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                // No cambiar las fechas, mantener las que el usuario ha seleccionado
                break;
        }

        // Actualizar también las fechas del formulario principal
        $this->fechaInicio = $this->fechaDesde;
        $this->fechaFin = $this->fechaHasta;

        $this->validarFechas();
    }

    public function validarFechas()
    {
        if (!$this->fechaDesde || !$this->fechaHasta) {
            $this->fechasValidas = false;
            return;
        }

        try {
            $fechaDesde = Carbon::parse($this->fechaDesde);
            $fechaHasta = Carbon::parse($this->fechaHasta);

            if ($fechaDesde->gt($fechaHasta)) {
                $this->fechasValidas = false;
                Notification::make()
                    ->title('Error en las fechas')
                    ->body('La fecha de inicio no puede ser mayor que la fecha de fin.')
                    ->danger()
                    ->send();
                return;
            }

            $this->fechasValidas = true;
        } catch (\Exception $e) {
            $this->fechasValidas = false;
        }
    }

    public function limpiarFiltros()
    {
        $this->periodoSeleccionado = 'hoy';
        $this->rutaSeleccionada = 'todas'; // Establecer valor por defecto
        $this->inicializarFiltroFecha();
        $this->cargarDatos();

        Notification::make()
            ->title('Filtros limpiados')
            ->body('Se han restablecido todos los filtros.')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        // Opciones de rutas según el usuario (manteniendo 'todas' por defecto)
        $rutasOptions = ['todas' => 'Todas las Rutas'];
        $user = auth()->user();

        if ($user && ($user->hasRole('Administrador') || $user->hasRole('Super Administrador'))) {
            // Admin y Super Admin: todas las rutas activas
            $rutas = \App\Models\Ruta::where('activa', true)->pluck('nombre', 'nombre');
        } else {
            // Otros roles: solo rutas asignadas al usuario
            $rutas = $user
                ? $user->rutas()->where('activa', true)->pluck('nombre', 'nombre')
                : collect();
        }

        $rutasOptions = array_merge($rutasOptions, $rutas->toArray());

        return [
            Grid::make(1)
                ->schema([
                    Select::make('rutaSeleccionada')
                        ->label('Seleccionar Ruta')
                        ->options($rutasOptions)
                        ->default('todas')
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->cargarDatos()),
                ])
        ];
    }

    public function cargarDatos()
    {
        // Usar las fechas del filtro de fecha
        $fechaInicio = $this->fechaDesde ?? now()->format('Y-m-d');
        $fechaFin = $this->fechaHasta ?? now()->format('Y-m-d');

        $query = Abonos::query()
            ->whereDate('fecha_pago', '>=', $fechaInicio)
            ->whereDate('fecha_pago', '<=', $fechaFin);

        // Filtrar por ruta si no es "todas"
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $query->whereHas('credito.cliente', function ($q) {
                $q->whereHas('ruta', function ($rutaQ) {
                    $rutaQ->where('nombre', $this->rutaSeleccionada);
                });
            });
        }

        // Cantidad Abonado
        $this->cantidadAbonado = $query->sum('monto_abono');

        // Total por tipo de pago usando ConceptoAbono
        $conceptosQuery = ConceptoAbono::query()
            ->whereHas('abono', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereDate('fecha_pago', '>=', $fechaInicio)
                  ->whereDate('fecha_pago', '<=', $fechaFin);
                if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
                    $q->whereHas('credito.cliente', function ($subQ) {
                        $subQ->whereHas('ruta', function ($rutaQ) {
                            $rutaQ->where('nombre', $this->rutaSeleccionada);
                        });
                    });
                }
            });

        $this->totalYapeado = (clone $conceptosQuery)->where('tipo_concepto', 'Yape')->sum('monto');
        $this->totalEfectivo = (clone $conceptosQuery)->where('tipo_concepto', 'Efectivo')->sum('monto');
        $this->totalTransferencia = (clone $conceptosQuery)->where('tipo_concepto', 'Transferencia')->sum('monto');

        // Cargar datos de créditos para el reporte de préstamos entregados
        $creditosQuery = Creditos::query()
            ->with(['cliente.ruta', 'tipoPago', 'conceptosCredito'])
            ->whereDate('fecha_credito', '>=', $fechaInicio)
            ->whereDate('fecha_credito', '<=', $fechaFin);

        // Filtrar por ruta si no es "todas"
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $creditosQuery->whereHas('cliente.ruta', function ($rutaQ) {
                $rutaQ->where('nombre', $this->rutaSeleccionada);
            });
        }

        $this->datosCreditos = $creditosQuery->get();

        // Construir registros detallados de créditos para modal de préstamos por ruta
        $creditosRecords = [];
        foreach ($this->datosCreditos as $credito) {
            $creditosRecords[] = [
                'monto' => (float) ($credito->valor_credito ?? 0),
                'fecha' => $credito->fecha_credito ? $credito->fecha_credito->format('Y-m-d H:i') : null,
                'fecha_ts' => $credito->fecha_credito ? $credito->fecha_credito->timestamp : null,
                'cliente' => optional($credito->cliente)->nombreCompleto ?? null,
                'ruta' => optional(optional($credito->cliente)->ruta)->nombre ?? null,
                'ruta_id' => optional(optional($credito->cliente)->ruta)->id_ruta ?? null,
                'tipo_pago' => optional($credito->tipoPago)->nombre ?? null,
                // Conceptos registrados al crear el crédito (forma de entrega)
                'conceptos' => ($credito->conceptosCredito ?? collect())
                    ->map(fn($c) => [
                        'tipo' => $c->tipo_concepto,
                        'monto' => (float) $c->monto,
                    ])->values()->toArray(),
            ];
        }
        $this->creditosRecords = $creditosRecords;

        // Cargar datos de abonos para los reportes
        $this->datosAbonos = $query->with(['credito.cliente.ruta', 'conceptosabonos'])->get();

        // NUEVO: Cargar conceptos de abono sin id_abono (movimientos independientes)
        $conceptosSinAbonoQuery = ConceptoAbono::query()
            ->whereNull('id_abono') // Solo los que NO tienen id_abono
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin);

        // Filtrar por ruta si no es "todas" (usando id_ruta directamente)
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $conceptosSinAbonoQuery->whereHas('ruta', function ($rutaQ) {
                $rutaQ->where('nombre', $this->rutaSeleccionada);
            });
        }

        $this->conceptosSinAbono = $conceptosSinAbonoQuery->get();

        // Construir registros detallados para el modal (concepto, monto, fecha, cliente, usuario, ruta)
        $records = [];
        foreach ($this->datosAbonos as $abono) {
            foreach ($abono->conceptosabonos as $conceptoAbono) {
                $records[] = [
                    'concepto' => $conceptoAbono->tipo_concepto,
                    'monto' => (float) $conceptoAbono->monto,
                    'fecha' => ($abono->fecha_pago ?? $abono->created_at) ? ($abono->fecha_pago ?? $abono->created_at)->format('Y-m-d H:i') : null,
                    'cliente' => optional($abono->cliente)->nombreCompleto ?? null,
                    'usuario' => optional($abono->usuario)->name ?? null,
                    'ruta' => optional($abono->ruta)->nombre ?? optional(optional($abono->credito)->cliente)->ruta->nombre ?? ($abono->id_ruta ?? null),
                    'ruta_id' => $abono->id_ruta ?? optional($abono->ruta)->id_ruta ?? optional(optional($abono->credito)->cliente)->ruta->id_ruta ?? null,
                    'fecha_ts' => ($abono->fecha_pago ?? $abono->created_at) ? ($abono->fecha_pago ?? $abono->created_at)->timestamp : null,
                    'origen' => optional($abono->concepto)->nombre ?? optional($abono->concepto)->tipo ?? 'Abono',
                    // NUEVO: bandera para saber si es devolución
                    'es_devolucion' => (bool) ($abono->es_devolucion ?? false),
                ];
            }
        }
        foreach ($this->conceptosSinAbono as $conceptoAbono) {
            $records[] = [
                'concepto' => $conceptoAbono->tipo_concepto,
                'monto' => (float) $conceptoAbono->monto,
                'fecha' => $conceptoAbono->fecha_concepto ? $conceptoAbono->fecha_concepto->format('Y-m-d H:i') : ($conceptoAbono->created_at ? $conceptoAbono->created_at->format('Y-m-d H:i') : null),
                'cliente' => null,
                'usuario' => optional($conceptoAbono->usuario)->name ?? null,
                'ruta' => optional($conceptoAbono->ruta)->nombre ?? ($conceptoAbono->id_ruta ?? null),
                'ruta_id' => $conceptoAbono->id_ruta ?? optional($conceptoAbono->ruta)->id ?? null,
                'fecha_ts' => $conceptoAbono->fecha_concepto ? $conceptoAbono->fecha_concepto->timestamp : ($conceptoAbono->created_at ? $conceptoAbono->created_at->timestamp : null),
                'origen' => optional($conceptoAbono->concepto)->nombre ?? optional($conceptoAbono->concepto)->tipo ?? 'Abono',
                'referencia' => $conceptoAbono->referencia ?? null,
                // Movimientos independientes no son devolución
                'es_devolucion' => false,
            ];
        }
        $this->records = $records;

        // Clientes atendidos y pendientes
        $clientesQuery = Clientes::query();
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $clientesQuery->whereHas('ruta', function ($rutaQ) {
                $rutaQ->where('nombre', $this->rutaSeleccionada);
            });
        }

        $this->clientesAtendidos = (clone $clientesQuery)->whereHas('creditos.abonos', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereDate('fecha_pago', '>=', $fechaInicio)
              ->whereDate('fecha_pago', '<=', $fechaFin);
        })->count();

        $this->clientesPendientes = (clone $clientesQuery)->whereHas('creditos', function ($q) {
            $q->where('saldo_actual', '>', 0);
        })->whereDoesntHave('creditos.abonos', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereDate('fecha_pago', '>=', $fechaInicio)
              ->whereDate('fecha_pago', '<=', $fechaFin);
        })->count();

        // Cargar deudas de alquiler por edificio (siempre listar todos los edificios)
        $this->cargarDeudaAlquilerPorEdificio();
    }





    public function getReporteActual()
    {
        $reportes = [
            'dashboard' => [
                'titulo' => 'Lista de Reportes',
                'color' => 'blue',
                'valor' => 'Selecciona un reporte para ver los detalles'
            ],
            'cantidad_abonado' => [
                'titulo' => 'Cantidad Abonado',
                'color' => 'blue',
                'valor' => 'S/ ' . number_format($this->cantidadAbonado, 2)
            ],
            'total_yapeado' => [
                'titulo' => 'Total Yapeado del Día',
                'color' => 'green',
                'valor' => 'S/ ' . number_format($this->totalYapeado, 2)
            ],
            'prestamos_entregados' => [
                'titulo' => 'Préstamos Entregados',
                'color' => 'orange',
                'valor' => $this->clientesAtendidos . ' préstamos'
            ],
            'reporte_abonos' => [
                'titulo' => 'Reporte de Abonos',
                'color' => 'purple',
                'valor' => $this->clientesPendientes . ' registros'
            ]
        ];

        return $reportes[$this->reporteSeleccionado] ?? $reportes['dashboard'];
    }

    /**
     * Calcular la deuda de alquiler por edificio sumando deudas por departamento y mes.
     * Lista todos los edificios activos, con cantidad de departamentos y deuda total.
     */
    protected function cargarDeudaAlquilerPorEdificio(): void
    {
        try {
            $edificios = Edificio::query()
                ->where('activo', true)
                ->with(['departamentos' => function($q){
                    $q->where('activo', true);
                }])
                ->orderBy('nombre')
                ->get();

            $resultado = [];
            foreach ($edificios as $edificio) {
                $departamentos = $edificio->departamentos ?? collect();
                $cantidadDepartamentos = $departamentos->count();

                $totalDeudaEdificio = 0.0;
                $detallePorMes = []; // key: YYYY-MM => ['label'=>..., 'monto'=>float]
                $departamentosDetalle = [];

                foreach ($departamentos as $departamento) {
                    // Alquiler activo del departamento
                    $alquiler = Alquiler::where('id_departamento', $departamento->id_departamento)
                        ->where('estado_alquiler', 'activo')
                        ->first();

                    if (!$alquiler) {
                        continue;
                    }

                    $precioMensual = (float) ($alquiler->precio_mensual ?? 0);
                    $fechaInicio = $alquiler->fecha_inicio ? Carbon::parse($alquiler->fecha_inicio)->copy()->startOfMonth() : null;
                    if (!$fechaInicio) {
                        continue;
                    }
                    $fechaFinAlquiler = $alquiler->fecha_fin ? Carbon::parse($alquiler->fecha_fin)->copy()->startOfMonth() : null;
                    $fechaLimite = $fechaFinAlquiler && $fechaFinAlquiler->lt(Carbon::now()->startOfMonth())
                        ? $fechaFinAlquiler
                        : Carbon::now()->startOfMonth();

                    $cursorMes = $fechaInicio->copy();
                    $detalleMesesDepto = [];
                    while ($cursorMes->lte($fechaLimite)) {
                        $year = (int) $cursorMes->year;
                        $month = (int) $cursorMes->month;
                        $key = $cursorMes->format('Y-m');
                        $label = ucfirst($cursorMes->locale('es')->isoFormat('MMMM YYYY'));

                        // Pagos del mes para este alquiler
                        $pagadoMes = (float) PagoAlquiler::where('id_alquiler', $alquiler->id_alquiler)
                            ->where('mes_correspondiente', $month)
                            ->where('ano_correspondiente', $year)
                            ->sum('monto_pagado');

                        $deudaMes = max($precioMensual - $pagadoMes, 0);
                        // Guardar detalle por departamento (incluye meses con deuda positiva)
                        $detalleMesesDepto[] = [
                            'label' => $label,
                            'monto' => round($deudaMes, 2),
                        ];

                        if (!isset($detallePorMes[$key])) {
                            $detallePorMes[$key] = [
                                'label' => $label,
                                'monto' => 0.0,
                            ];
                        }
                        $detallePorMes[$key]['monto'] += $deudaMes;
                        $totalDeudaEdificio += $deudaMes;

                        $cursorMes->addMonth();
                    }

                    // Agregar entrada del departamento
                    $departamentosDetalle[] = [
                        'departamento_id' => $departamento->id_departamento,
                        'departamento' => 'Depto. ' . ($departamento->numero_departamento ?? ''),
                        'meses' => $detalleMesesDepto,
                    ];
                }

                ksort($detallePorMes);
                $resultado[] = [
                    'edificio_id' => $edificio->id_edificio,
                    'edificio' => $edificio->nombre,
                    'cantidad' => $cantidadDepartamentos,
                    'monto_total' => round($totalDeudaEdificio, 2),
                    'detalle' => array_values($detallePorMes), // resumen mensual del edificio
                    'departamentos_detalle' => $departamentosDetalle, // detalle por departamento
                ];
            }

            $this->deudaAlquilerPorEdificio = $resultado;
        } catch (\Throwable $e) {
            // En caso de error, evitar romper la página
            $this->deudaAlquilerPorEdificio = [];
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\ConceptoAbono;
use App\Models\Ruta;
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

class ReportesCristian extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes Cristian';
    protected static ?string $title = 'Panel de Reportes';
    protected static ?string $slug = 'reportes-cristian';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Movimientos';
    protected static bool $shouldRegisterNavigation = true;

    protected static string $view = 'filament.pages.reportes-cristian';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('Listar Reportes Cristian');
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

    public function mount()
    {
        $this->fechaInicio = Carbon::today()->format('Y-m-d');
        $this->fechaFin = Carbon::today()->format('Y-m-d');
        $this->inicializarFiltroFecha();
        $this->cargarDatos();
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
        $this->rutaSeleccionada = null;
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
        // Obtener todas las rutas de la base de datos
        $rutasOptions = ['todas' => 'Todas las Rutas'];
        $rutas = \App\Models\Ruta::where('activa', true)->pluck('nombre', 'nombre');
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
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);

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
                $q->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
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
            ->with(['cliente.ruta', 'tipoPago'])
            ->whereBetween('fecha_credito', [$fechaInicio, $fechaFin]);

        // Filtrar por ruta si no es "todas"
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $creditosQuery->whereHas('cliente.ruta', function ($rutaQ) {
                $rutaQ->where('nombre', $this->rutaSeleccionada);
            });
        }

        $this->datosCreditos = $creditosQuery->get();

        // Cargar datos de abonos para los reportes
        $this->datosAbonos = $query->with(['credito.cliente.ruta', 'conceptosabonos'])->get();

        // Clientes atendidos y pendientes
        $clientesQuery = Clientes::query();
        if ($this->rutaSeleccionada && $this->rutaSeleccionada !== 'todas') {
            $clientesQuery->whereHas('ruta', function ($rutaQ) {
                $rutaQ->where('nombre', $this->rutaSeleccionada);
            });
        }

        $this->clientesAtendidos = (clone $clientesQuery)->whereHas('creditos.abonos', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        })->count();

        $this->clientesPendientes = (clone $clientesQuery)->whereHas('creditos', function ($q) {
            $q->where('saldo_actual', '>', 0);
        })->whereDoesntHave('creditos.abonos', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        })->count();
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
}

<?php

namespace App\Filament\Pages;

use App\Exports\ClienteCreditosAbonosExport;
use App\Filament\Widgets\ClienteCreditosAbonosWidget;
use App\Models\User;
use App\Models\Ruta;
use App\Http\Livewire\Traits\RouteValidation;
use Filament\Forms\Components\Actions\Modal\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ClienteCreditosAbonos extends Page
{
    use RouteValidation;
    protected static ?string $navigationIcon = 'heroicon-o-document-report';
    protected static ?string $navigationLabel = 'Liquidaciones';
    protected static ?string $title = 'Liquidaciones por Ruta';
    protected static ?string $slug = 'liquidaciones';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Movimientos';
    protected static bool $shouldRegisterNavigation = false;
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->can('Listar Liquidaciones');
    }
    
    protected static string $view = 'filament.pages.cliente-creditos-abonos';
    
    protected $listeners = ['sincronizar-filtros' => 'sincronizarFiltros'];
    
    public ?int $rutaId = null;
    public $rutas = [];
    
    // Propiedades para filtro de fechas
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy';
    public bool $fechasValidas = true;
    
    public function mount(): void
    {
        // Validar y corregir la ruta seleccionada usando el trait
        $this->validateAndCorrectSelectedRoute();

        // Cargar rutas que tienen usuarios asignados
        $this->rutas = Ruta::whereHas('usuarios')
            ->orderBy('nombre')
            ->get()
            ->map(function ($ruta) {
                $usuariosCount = $ruta->usuarios()->count();
                return [
                    'id' => $ruta->id_ruta,
                    'nombre' => $ruta->nombre . ' (' . $usuariosCount . ' usuario' . ($usuariosCount > 1 ? 's' : '') . ')',
                ];
            })
            ->pluck('nombre', 'id')
            ->toArray();
        
        // Sincronizar la ruta inicial desde la sesión (si existe)
        $this->rutaId = Session::get('selected_ruta_id');
        if ($this->rutaId) {
            $this->actualizarRuta();
        }
            
        // Aplicar período por defecto
        $this->aplicarPeriodo();
    }
    
    public function aplicarPeriodo(): void
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
            case 'semana_actual':
                $this->fechaDesde = $hoy->copy()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaDesde = $hoy->copy()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                // No cambiar las fechas si es personalizado
                break;
        }
        
        $this->validarFechas();
    }
    
    public function validarFechas(): void
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            $this->fechasValidas = $this->fechaDesde <= $this->fechaHasta;
        } else {
            $this->fechasValidas = true;
        }
    }
    
    public function limpiarFiltros(): void
    {
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->periodoSeleccionado = 'hoy';
        $this->aplicarPeriodo();
        $this->fechasValidas = true;
    }
    
    public function updated($name)
    {
        if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            if ($name === 'periodoSeleccionado') {
                $this->aplicarPeriodo();
            } else {
                $this->validarFechas();
            }
            
            // Emitir evento para sincronizar con el widget
            $this->emit('filtros-actualizados', [
                'fechaDesde' => $this->fechaDesde,
                'fechaHasta' => $this->fechaHasta,
                'periodoSeleccionado' => $this->periodoSeleccionado
            ]);
        }
    }
    
    public function sincronizarFiltros($filtros)
    {
        $this->fechaDesde = $filtros['fechaDesde'] ?? null;
        $this->fechaHasta = $filtros['fechaHasta'] ?? null;
        $this->periodoSeleccionado = $filtros['periodoSeleccionado'] ?? 'hoy';
        $this->validarFechas();
    }
    /*
    protected function getHeaderWidgets(): array
    {
        return [
            ClienteCreditosAbonosWidget::class,
        ];
    }
    */
    public function actualizarRuta(): void
    {
        if ($this->rutaId) {
            $this->emit('ruta-seleccionada', $this->rutaId);
        }
    }
    
    public function handleRutaSeleccionada($rutaId): void
    {
        $this->rutaId = $rutaId;
    }

    public function exportToPDF()
    {
        try {
            if (!$this->rutaId) {
                $this->notify('warning', 'Debe seleccionar una ruta primero');
                return;
            }

            Log::info('Iniciando exportación PDF para ruta: ' . $this->rutaId, [
                'fechaDesde' => $this->fechaDesde,
                'fechaHasta' => $this->fechaHasta
            ]);

            $export = new ClienteCreditosAbonosExport($this->rutaId, true, $this->fechaDesde, $this->fechaHasta);
            return $export->exportToPDF();

        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            $this->notify('danger', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /*
        TODAVIA NO ESTA CONFIGURADO PARA QUE SE PUEDA HACER EXPORTACIONES

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportarPDF')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->hidden(!$this->userId)
                ->url(fn () => route('liquidaciones.export.pdf', ['userId' => $this->userId]))
                ->openUrlInNewTab(),
                
            Action::make('exportarExcel')
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->hidden(!$this->userId)
                ->url(fn () => route('liquidaciones.export.excel', ['userId' => $this->userId]))
                ->openUrlInNewTab(),
        ];
    }

*/
    
    protected function getFormSchema(): array
    {
        return [
            Select::make('rutaId')
                ->label('Ruta')
                ->options($this->rutas)
                ->placeholder('Seleccione una ruta')
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->actualizarRuta();
                }),
        ];
    }
}
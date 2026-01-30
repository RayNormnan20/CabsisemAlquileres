<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Http\Livewire\Traits\RouteValidation;
use App\Models\ConceptoAbono;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\ConceptoAbonoWebSocketWidget;
use App\Models\User;
use Illuminate\Contracts\View\View;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListConceptosAbonos extends ListRecords
{
    use RouteValidation;
    protected static string $resource = ConceptosAbonosResource::class;

    // Filtros
    public ?int $userId = null;
    public $fechaDesde;
    public $fechaHasta;
    public $fechaPeriodo = 'hoy';
    public string $tipoFecha = 'created_at';

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        '$refresh',
    ];

    public function mount(): void
    {
        parent::mount();
        // Validar/corregir la ruta en sesión antes de construir la consulta
        $this->validateAndCorrectSelectedRoute();
        $this->aplicarPeriodoFecha('hoy');
    }

    public function aplicarPeriodoFecha($periodo)
    {
        $this->fechaPeriodo = $periodo;

        switch ($periodo) {
            case 'hoy':
                $this->fechaDesde = now()->format('Y-m-d');
                $this->fechaHasta = now()->format('Y-m-d');
                break;
            case 'ayer':
                $this->fechaDesde = now()->subDay()->format('Y-m-d');
                $this->fechaHasta = now()->subDay()->format('Y-m-d');
                break;
            case 'semana':
                $this->fechaDesde = now()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'semana_anterior':
                $this->fechaDesde = now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = now()->subWeeks(2)->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes':
                $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fechaDesde = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                // No cambiar las fechas, mantener las que el usuario ha seleccionado
                break;
        }
    }

    public function resetearFiltros()
    {
        $this->userId = null;
        $this->aplicarPeriodoFecha('hoy');
        // $this->resetPage(); // Paginación desactivada
    }

    public function updatedUserId($value)
    {
        // $this->resetPage();
    }

    public function updatedFechaPeriodo($value)
    {
        // $this->resetPage();
    }
    
    public function updatedTipoFecha($value)
    {
        // $this->resetPage();
    }

    protected function isTablePaginationEnabled(): bool 
    {
        return false;
    }

    protected function getHeader(): View
    {
        // Obtener la ruta seleccionada de la sesión
        $rutaSeleccionada = Session::get('selected_ruta_id');

        $usersQuery = User::orderBy('name');

        // Filtrar usuarios asignados a la ruta seleccionada
        if ($rutaSeleccionada) {
            $usersQuery->whereHas('rutas', function ($query) use ($rutaSeleccionada) {
                $query->where('ruta.id_ruta', $rutaSeleccionada);
            });
        }

        $users = $usersQuery->pluck('name', 'id');

        return view('filament.resources.conceptos-abonos-resource.header', [
            'users' => $users,
            'userId' => $this->userId,
            'fechaPeriodo' => $this->fechaPeriodo,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'tipoFecha' => $this->tipoFecha,
        ]);
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        // Al cambiar la ruta, reiniciar la paginación para reflejar el nuevo filtro
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function getTableQuery(): Builder
    {
        $query = ConceptoAbono::query()
            ->whereNull('id_abono');
            
        $rutaSeleccionada = Session::get('selected_ruta_id');
        if ($rutaSeleccionada) {
            $query->where('id_ruta', $rutaSeleccionada);
        }

        // Filtro de Usuario
        if ($this->userId) {
            $query->where('id_usuario', $this->userId);
        }

        // Filtro de Fechas
        if ($this->fechaDesde) {
            $campoFecha = $this->tipoFecha === 'fecha_concepto' ? 'fecha_concepto' : 'created_at';
            $query->whereDate($campoFecha, '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $campoFecha = $this->tipoFecha === 'fecha_concepto' ? 'fecha_concepto' : 'created_at';
            $query->whereDate($campoFecha, '<=', $this->fechaHasta);
        }
        
        return $query;
    }
       protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // Esto oculta el botón "Crear Concepto"
        ];
    }

    // Desactivar completamente acciones masivas y ocultar los checkboxes de selección
    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ConceptoAbonoWebSocketWidget::class,
        ];
    }
}
<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use App\Filament\Widgets\AlquileresWebSocketWidget;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\Alquiler;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListAlquileres extends ListRecords
{
    protected static string $resource = AlquileresResource::class;

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        'refreshAlquileresTable' => '$refresh',
        '$refresh',
    ];

    // Propiedades para filtros de edificio y departamento
    public ?int $edificioId = null;
    public ?int $departamentoId = null;
    public $edificios;
    public $departamentos;
    public $edificio = null;
    public $departamento = null;
    public $alquileresActivos = 0;

    protected function getHeaderWidgets(): array
    {
        return [
            AlquileresWebSocketWidget::class,
        ];
    }

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    public function mount(): void
    {
        parent::mount();

        if (Session::has('selected_ruta_id')) {
            $this->currentRutaId = Session::get('selected_ruta_id');
            $this->currentRutaName = Session::get('selected_ruta_name');
        } else {
            $this->currentRutaId = null;
            $this->currentRutaName = 'Todas las Rutas';
        }

        $this->cargarEdificios();

        // Inicializar departamentos como colección vacía
        if (!$this->departamentos) {
            $this->departamentos = collect();
        }
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';
        $this->resetPage();
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->currentRutaId) {
            $query->whereHas('inquilino', function($q) {
                $q->where('id_ruta', $this->currentRutaId);
            });
        }

        // Filtro por edificio
        if ($this->edificioId) {
            $query->whereHas('departamento', function($q) {
                $q->where('id_edificio', $this->edificioId);
            });
        }

        // Filtro por departamento específico
        if ($this->departamentoId) {
            $query->where('id_departamento', $this->departamentoId);
        }

        return $query;
    }

    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Alquileres - {$this->currentRutaName}";
        }
        return "Listado de Alquileres";
    }



    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    // Métodos para manejar filtros de edificio y departamento
    public function cargarEdificios()
    {
        $this->edificios = Edificio::where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn($e) => [$e->id_edificio => $e->nombre]);

        // Si no hay edificios, inicializar como colección vacía
        if (!$this->edificios) {
            $this->edificios = collect();
        }
    }

    public function updatedEdificioId($value)
    {
        $this->departamentoId = null;
        $this->departamento = null;
        $this->departamentos = collect();

        if ($value) {
            $this->edificio = Edificio::with(['propietario', 'departamentos.estado'])->find($value);
            $this->cargarDepartamentos($value);
            $this->calcularAlquileresActivos();
        } else {
            $this->edificio = null;
            $this->alquileresActivos = 0;
        }

        $this->resetPage();
    }

    public function updatedDepartamentoId($value)
    {
        if ($value) {
            $this->departamento = Departamento::with(['edificio', 'estado', 'alquilerActivo.inquilino'])->find($value);
        } else {
            $this->departamento = null;
        }

        $this->resetPage();
    }

    public function cargarDepartamentos($edificioId)
    {
        $this->departamentos = Departamento::where('id_edificio', $edificioId)
            ->where('activo', true)
            ->orderBy('numero_departamento')
            ->get()
            ->mapWithKeys(fn($d) => [
                $d->id_departamento => "Depto. {$d->numero_departamento} - Piso {$d->piso}"
            ]);
    }

    public function calcularAlquileresActivos()
    {
        if ($this->edificioId) {
            $this->alquileresActivos = Alquiler::whereHas('departamento', function($q) {
                $q->where('id_edificio', $this->edificioId);
            })
            ->where('estado_alquiler', 'activo')
            ->count();
        }
    }

    public function resetearFiltros()
    {
        $this->edificioId = null;
        $this->departamentoId = null;
        $this->edificio = null;
        $this->departamento = null;
        $this->departamentos = collect();
        $this->alquileresActivos = 0;
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Alquiler')
                ->icon('heroicon-s-plus'),
        ];
    }

    protected function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.resources.alquileres-resource.pages.header', [
            'edificios' => $this->edificios,
            'departamentos' => $this->departamentos,
            'edificioId' => $this->edificioId,
            'departamentoId' => $this->departamentoId,
            'edificio' => $this->edificio,
            'departamento' => $this->departamento,
            'alquileresActivos' => $this->alquileresActivos,
        ]);
    }
}

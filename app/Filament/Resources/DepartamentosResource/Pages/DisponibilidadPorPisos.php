<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use App\Models\Departamento;
use App\Models\Edificio;
use App\Models\EstadoDepartamento;
use Filament\Resources\Pages\Page;
use Filament\Pages\Actions;
use Livewire\Component;

class DisponibilidadPorPisos extends Page
{
    protected static string $resource = DepartamentosResource::class;
    protected static string $view = 'filament.resources.departamentos-resource.pages.disponibilidad-por-pisos';
    
    protected static ?string $navigationIcon = 'heroicon-o-view-grid';
    protected static ?string $title = 'Disponibilidad por Pisos';
    
    public $selectedEdificio = null;
    public $edificios = [];
    public $departamentosPorPiso = [];
    public $estadosColores = [];
    public $todosDepartamentos = [];
    
    public function mount(): void
    {
        $this->edificios = Edificio::where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->pluck('nombre', 'id_edificio')
            ->toArray();
            
        $this->estadosColores = EstadoDepartamento::where('activo', true)
            ->get()
            ->pluck('color', 'nombre')
            ->toArray();
            
        // Seleccionar el primer edificio por defecto
        if (!empty($this->edificios)) {
            $this->selectedEdificio = array_key_first($this->edificios);
            $this->loadDepartamentos();
            $this->loadTodosDepartamentos();
        }
    }
    
    public function updatedSelectedEdificio()
    {
        $this->loadDepartamentos();
        $this->loadTodosDepartamentos();
    }
    
    protected function loadDepartamentos()
    {
        if (!$this->selectedEdificio) {
            $this->departamentosPorPiso = [];
            return;
        }
        
        $departamentos = Departamento::with([
                'estado', 
                'edificio',
                'alquileres' => function($query) {
                    $query->where('estado_alquiler', 'activo')
                          ->with('inquilino');
                }
            ])
            ->where('id_edificio', $this->selectedEdificio)
            ->where('activo', true)
            ->orderBy('piso', 'desc')
            ->orderBy('numero_departamento')
            ->get();
            
        $this->departamentosPorPiso = $departamentos->groupBy('piso')
            ->sortKeysDesc()
            ->map(function ($departamentos) {
                return $departamentos->sortBy('numero_departamento')->values()->map(function ($departamento) {
                    // Convertir a array manualmente para incluir relaciones anidadas
                    $data = $departamento->toArray();
                    
                    // Asegurar que las relaciones de alquileres se incluyan correctamente
                    if ($departamento->alquileres) {
                        $data['alquileres'] = $departamento->alquileres->map(function ($alquiler) {
                            $alquilerData = $alquiler->toArray();
                            if ($alquiler->inquilino) {
                                $alquilerData['inquilino'] = $alquiler->inquilino->toArray();
                            }
                            return $alquilerData;
                        })->toArray();
                    }
                    
                    return $data;
                });
            })
            ->toArray();
    }
    
    protected function loadTodosDepartamentos()
    {
        if (!$this->selectedEdificio) {
            $this->todosDepartamentos = [];
            return;
        }
        
        $this->todosDepartamentos = Departamento::with(['estado', 'edificio'])
            ->where('id_edificio', $this->selectedEdificio)
            ->where('activo', true)
            ->orderBy('numero_departamento')
            ->get()
            ->toArray();
    }
    
    protected function getViewData(): array
    {
        return [
            'edificios' => $this->edificios,
            'selectedEdificio' => $this->selectedEdificio,
            'departamentosPorPiso' => $this->departamentosPorPiso,
            'estadosColores' => $this->estadosColores,
            'todosDepartamentos' => $this->todosDepartamentos,
        ];
    }
    
    public function getBackgroundStyle($hexColor): string
    {
        // Retornar el color hexadecimal directamente para usar como estilo inline
        return 'background-color: ' . $hexColor;
    }
    
    protected function getActions(): array
    {
        return [
            Actions\Action::make('crear')
                ->label('Agregar Departamento')
                ->icon('heroicon-s-plus')
                ->color('primary')
                ->url(fn () => static::getResource()::getUrl('create')),
            Actions\Action::make('listado')
                ->label('Ver Listado Tradicional')
                ->icon('heroicon-o-table')
                ->color('secondary')
                ->url(fn () => static::getResource()::getUrl('listado')),
        ];
    }
}
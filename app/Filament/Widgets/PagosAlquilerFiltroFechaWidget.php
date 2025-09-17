<?php

namespace App\Filament\Widgets;

use App\Models\Edificio;
use App\Models\Departamento;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Carbon\Carbon;

class PagosAlquilerFiltroFechaWidget extends Widget implements HasForms
{
    use InteractsWithForms;
    
    protected static string $view = 'filament.widgets.pagos-alquiler-filtro-fecha';
    
    protected int | string | array $columnSpan = 'full';
    
    // Propiedades para filtro de fecha
    public $periodoSeleccionado = 'hoy';
    public $fechaDesde;
    public $fechaHasta;
    public bool $fechasValidas = true;
    
    // Propiedades para filtros de búsqueda
    public $edificioSeleccionado = null;
    public $departamentoSeleccionado = null;

    public function mount(): void
    {
        $this->aplicarPeriodo();
        $this->form->fill();
    }
    
    public function updatedPeriodoSeleccionado(): void
    {
        $this->aplicarPeriodo();
    }
    
    public function updated($name): void
    {
        if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            if ($name === 'periodoSeleccionado') {
                $this->aplicarPeriodo();
            } else {
                $this->validarFechas();
            }
            
            // Emitir evento para sincronizar con la página
            $this->emit('filtros-actualizados', [
                'fechaDesde' => $this->fechaDesde,
                'fechaHasta' => $this->fechaHasta,
                'periodoSeleccionado' => $this->periodoSeleccionado
            ]);
        }
    }
    
    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('edificioSeleccionado')
                        ->label('Edificio')
                        ->placeholder('Seleccionar edificio')
                        ->options($this->getEdificios())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->departamentoSeleccionado = null;
                            $this->emitUp('filtros-edificio-actualizados', [
                                'edificio' => $state,
                                'departamento' => null
                            ]);
                        }),

                    Select::make('departamentoSeleccionado')
                        ->label('Departamento')
                        ->placeholder('Seleccionar departamento')
                        ->options($this->getDepartamentos())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->emitUp('filtros-edificio-actualizados', [
                                'edificio' => $this->edificioSeleccionado,
                                'departamento' => $state
                            ]);
                        })
                        ->disabled(fn () => !$this->edificioSeleccionado),
                ])
        ];
    }
    
    protected function getEdificios(): array
    {
        return Edificio::where('activo', true)
            ->orderBy('nombre')
            ->pluck('nombre', 'id_edificio')
            ->toArray();
    }

    protected function getDepartamentos(): array
    {
        if (!$this->edificioSeleccionado) {
            return [];
        }

        return Departamento::where('id_edificio', $this->edificioSeleccionado)
            ->where('activo', true)
            ->orderBy('numero_departamento')
            ->pluck('numero_departamento', 'id_departamento')
            ->toArray();
    }

    public function updatedEdificioSeleccionado($value)
    {
        $this->departamentoSeleccionado = null;
    }
    
    public function aplicarPeriodo(): void
    {
        $hoy = Carbon::now();
        
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
                $semanaPasada = $hoy->copy()->subWeek();
                $this->fechaDesde = $semanaPasada->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $semanaPasada->endOfWeek()->format('Y-m-d');
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = $hoy->copy()->subWeeks(2)->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'este_mes':
                $this->fechaDesde = $hoy->copy()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_pasado':
                $mesPasado = $hoy->copy()->subMonth();
                $this->fechaDesde = $mesPasado->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $mesPasado->endOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                // No modificar las fechas cuando es personalizado
                // El usuario las establecerá manualmente
                break;
        }
        
        $this->validarFechas();
    }
    
    public function validarFechas(): void
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            $this->fechasValidas = Carbon::parse($this->fechaDesde)->lte(Carbon::parse($this->fechaHasta));
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
        
        // Emitir evento para sincronizar con la página
        $this->emit('filtros-actualizados', [
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'periodoSeleccionado' => $this->periodoSeleccionado
        ]);
    }
    
    public function exportarPdf()
    {
        $this->emitUp('exportar-pdf');
    }
    
    public function getPeriodosProperty()
    {
        return [
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'esta_semana' => 'Esta semana',
            'semana_pasada' => 'Semana pasada',
            'ultimas_2_semanas' => 'Últimas 2 semanas',
            'este_mes' => 'Este mes',
            'mes_pasado' => 'Mes pasado',
            'personalizado' => 'Personalizado'
        ];
    }
}
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Component;
use Carbon\Carbon;

class PagosAlquilerFiltroFechaWidget extends Widget
{
    protected static string $view = 'filament.widgets.pagos-alquiler-filtro-fecha';
    
    protected int | string | array $columnSpan = 'full';
    
    public $periodoSeleccionado = 'hoy';
    public $fechaDesde;
    public $fechaHasta;
    public bool $fechasValidas = true;
    
    public function mount()
    {
        // Establecer fecha de hoy por defecto
        $this->aplicarPeriodo();
        
        // Emitir evento inicial para sincronizar con la página
        $this->emit('filtros-actualizados', [
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'periodoSeleccionado' => $this->periodoSeleccionado
        ]);
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
    
    public function aplicarPeriodo()
    {
        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = Carbon::today()->format('Y-m-d');
                $this->fechaHasta = Carbon::today()->format('Y-m-d');
                break;
            case 'ayer':
                $this->fechaDesde = Carbon::yesterday()->format('Y-m-d');
                $this->fechaHasta = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'semana_actual':
                $this->fechaDesde = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fechaDesde = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                // Mantener las fechas actuales o usar valores por defecto
                if (!$this->fechaDesde) {
                    $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
                }
                if (!$this->fechaHasta) {
                    $this->fechaHasta = Carbon::now()->format('Y-m-d');
                }
                break;
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
    
    public function validarFechas(): void
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaDesde = Carbon::parse($this->fechaDesde);
            $fechaHasta = Carbon::parse($this->fechaHasta);
            
            if ($fechaDesde->gt($fechaHasta)) {
                $this->fechasValidas = false;
                return;
            }
        }
        
        $this->fechasValidas = true;
    }
    
    public function getPeriodosProperty()
    {
        return [
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'semana_actual' => 'Semana actual',
            'mes_actual' => 'Mes actual',
            'mes_anterior' => 'Mes anterior',
            'personalizado' => 'Rango personalizado'
        ];
    }
}
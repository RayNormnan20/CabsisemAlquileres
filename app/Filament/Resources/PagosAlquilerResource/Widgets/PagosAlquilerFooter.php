<?php

namespace App\Filament\Resources\PagosAlquilerResource\Widgets;

use App\Models\PagoAlquiler;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PagosAlquilerFooter extends Widget
{
    protected static string $view = 'filament.resources.pagos-alquiler-resource.pagos-footer';
    protected int|string|array $columnSpan = 'full';

    // Filtros sincronizados con la página/listado
    public $fechaDesde;
    public $fechaHasta;
    public $tipoFecha = 'created_at';
    public $edificioSeleccionado;
    public $departamentoSeleccionado;
    public ?int $currentRutaId = null;

    protected $listeners = [
        // Desde el widget de filtros
        'filtros-actualizados' => 'applyDateFilters',
        'filtros-edificio-actualizados' => 'applyBuildingFilters',
        // Re-emisión centralizada desde la página para sincronizar todo
        'pagos-alquiler-footer-filters' => 'applyCombinedFilters',
        'refreshComponent' => '$refresh',
    ];

    public function applyDateFilters(array $filters): void
    {
        $this->fechaDesde = $filters['fechaDesde'] ?? null;
        $this->fechaHasta = $filters['fechaHasta'] ?? null;
        $this->tipoFecha = $filters['tipoFecha'] ?? $this->tipoFecha;
        $this->emit('$refresh');
    }

    public function applyBuildingFilters(array $filters): void
    {
        $this->edificioSeleccionado = $filters['edificio'] ?? null;
        $this->departamentoSeleccionado = $filters['departamento'] ?? null;
        $this->emit('$refresh');
    }

    public function applyCombinedFilters(array $filters): void
    {
        $this->fechaDesde = $filters['fechaDesde'] ?? null;
        $this->fechaHasta = $filters['fechaHasta'] ?? null;
        $this->tipoFecha = $filters['tipoFecha'] ?? $this->tipoFecha;
        $this->edificioSeleccionado = $filters['edificio'] ?? null;
        $this->departamentoSeleccionado = $filters['departamento'] ?? null;
        $this->currentRutaId = $filters['rutaId'] ?? $this->currentRutaId;
        $this->emit('$refresh');
    }

    protected function getFilteredQuery(): Builder
    {
        $query = PagoAlquiler::query();

        // Determinar columna de fecha según selección
        $columnaFecha = $this->tipoFecha === 'fecha_pago' ? 'pagos_alquiler.fecha_pago' : 'pagos_alquiler.created_at';

        // Filtro por ruta seleccionada
        if ($this->currentRutaId) {
            $query->whereHas('alquiler.departamento', function ($q) {
                $q->where('id_ruta', $this->currentRutaId);
            });
        }

        // Filtros de fecha
        if ($this->fechaDesde) {
            $query->whereDate($columnaFecha, '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $query->whereDate($columnaFecha, '<=', $this->fechaHasta);
        }
        // Si no hay filtros, usar fecha del tipo seleccionado para hoy
        if (!$this->fechaDesde && !$this->fechaHasta) {
            $hoy = \Carbon\Carbon::today()->format('Y-m-d');
            $query->whereDate($columnaFecha, $hoy);
        }

        // Filtros de edificio/departamento
        if ($this->edificioSeleccionado) {
            $query->whereHas('alquiler.departamento', function ($q) {
                $q->where('id_edificio', $this->edificioSeleccionado);
            });
        }
        if ($this->departamentoSeleccionado) {
            $query->whereHas('alquiler', function ($q) {
                $q->where('id_departamento', $this->departamentoSeleccionado);
            });
        }

        return $query;
    }

    /**
     * Retorna colección agrupada por usuario con totales por método (efectivo/yape).
     */
    public function getUsuariosResumen()
    {
        $rows = $this->getFilteredQuery()
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                'pagos_alquiler.metodo_pago as metodo_pago',
                DB::raw('SUM(pagos_alquiler.monto_pagado) as total_monto'),
            ])
            ->join('users', 'pagos_alquiler.id_usuario_registro', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'pagos_alquiler.metodo_pago')
            ->get();

        return $rows->groupBy('user_id')->map(function ($items) {
            $name = optional($items->first())->user_name ?? 'Usuario';
            $efectivo = $items->filter(function ($r) {
                return strcasecmp($r->metodo_pago, 'efectivo') === 0;
            })->sum('total_monto');
            $yape = $items->filter(function ($r) {
                return strcasecmp($r->metodo_pago, 'yape') === 0;
            })->sum('total_monto');
            return [
                'name' => $name,
                'efectivo' => (float) $efectivo,
                'yape' => (float) $yape,
                'total' => (float) ($efectivo + $yape),
            ];
        })->values();
    }

    public function getTotalGeneral()
    {
        return $this->getUsuariosResumen()->sum('total');
    }
}
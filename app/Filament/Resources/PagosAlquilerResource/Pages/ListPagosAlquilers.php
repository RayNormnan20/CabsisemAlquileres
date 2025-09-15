<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use App\Filament\Widgets\PagosAlquilerWebSocketWidget;
use App\Filament\Widgets\PagosAlquilerFiltroFechaWidget;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ListPagosAlquilers extends ListRecords
{
    protected static string $resource = PagosAlquilerResource::class;

    public $fechaDesde;
    public $fechaHasta;
    public $periodoSeleccionado = 'hoy';
    public $edificioSeleccionado = null;
    public $departamentoSeleccionado = null;

    public function mount(): void
    {
        parent::mount();

        // Inicializar filtro de fecha por defecto (hoy)
        $this->fechaDesde = Carbon::today()->format('Y-m-d');
        $this->fechaHasta = Carbon::today()->format('Y-m-d');
    }

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        'refreshPagosAlquilerTable' => '$refresh',
        'filtrarPorFecha' => 'filtrarPorFecha',
        'limpiarFiltrosFecha' => 'limpiarFiltrosFecha',
        'filtros-actualizados' => 'sincronizarFiltros',
        'filtros-edificio-actualizados' => 'sincronizarFiltrosEdificio',
        'exportar-pdf' => 'exportarPDF',
        '$refresh',
    ];

    protected function getHeaderWidgets(): array
    {
        return [
            PagosAlquilerFiltroFechaWidget::class,
            PagosAlquilerWebSocketWidget::class,
            \App\Filament\Widgets\PagosAlquilerFiltrosWidget::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function filtrarPorFecha($data)
    {
        $this->fechaDesde = $data['fechaDesde'] ?? null;
        $this->fechaHasta = $data['fechaHasta'] ?? null;
        $this->resetTable();
    }

    public function limpiarFiltrosFecha()
    {
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->resetTable();
    }

    public function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Aplicar filtros de fecha si están definidos
        if ($this->fechaDesde) {
            $query->whereDate('fecha_pago', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $query->whereDate('fecha_pago', '<=', $this->fechaHasta);
        }

        // Aplicar filtros de edificio y departamento
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

    public function updated($name)
    {
        if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            if ($name === 'periodoSeleccionado') {
                // El widget se encarga de aplicar el período
            }
            $this->resetPage();
        }
    }

    public function sincronizarFiltros($filtros)
    {
        $this->fechaDesde = $filtros['fechaDesde'] ?? null;
        $this->fechaHasta = $filtros['fechaHasta'] ?? null;
        $this->periodoSeleccionado = $filtros['periodoSeleccionado'] ?? 'hoy';
        $this->resetPage();
    }

    public function sincronizarFiltrosEdificio($filtros)
    {
        $this->edificioSeleccionado = $filtros['edificio'] ?? null;
        $this->departamentoSeleccionado = $filtros['departamento'] ?? null;
        $this->resetPage();
    }

    public function exportarPDF()
    {
        // Obtener los datos filtrados
        $pagos = $this->getTableQuery()->with([
            'alquiler.departamento.edificio',
            'alquiler.inquilino',
            'usuarioRegistro'
        ])->get();

        if ($pagos->isEmpty()) {
            $this->notify('warning', 'No hay datos para exportar con los filtros aplicados.');
            return;
        }

        // Generar el PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('filament.exports.pagos-alquiler-pdf', [
            'pagos' => $pagos,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'edificioSeleccionado' => $this->edificioSeleccionado,
            'departamentoSeleccionado' => $this->departamentoSeleccionado,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ]);

        $nombreArchivo = 'pagos-alquiler-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }
}

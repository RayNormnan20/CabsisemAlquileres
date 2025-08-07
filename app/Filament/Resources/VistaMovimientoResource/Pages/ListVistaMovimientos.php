<?php

namespace App\Filament\Resources\VistaMovimientoResource\Pages;

use App\Filament\Resources\VistaMovimientoResource;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ListVistaMovimientos extends ListRecords
{
    protected static string $resource = VistaMovimientoResource::class;

    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'mes_actual';
    public bool $fechasValidas = true;

    public function mount(): void
    {
        parent::mount();
        $this->aplicarPeriodo();
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Agregar Ingreso o Gasto'),
        ];
    }

    protected function getHeader(): View
    {
        return view('filament.resources.vista-movimiento-resource.header', [
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'fechasValidas' => $this->fechasValidas
        ]);
    }

    protected function getTableQuery(): Builder
    {
        $this->validarFechas();

        if (!$this->fechasValidas) {
            return parent::getTableQuery()->whereRaw('1=0');
        }

        return parent::getTableQuery()
            ->when($this->fechaDesde, function (Builder $query) {
                return $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function (Builder $query) {
                return $query->whereDate('fecha', '<=', $this->fechaHasta);
            });
    }

    public function aplicarPeriodo(): void
    {
        $hoy = Carbon::today();

        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = $hoy->toDateString();
                $this->fechaHasta = $hoy->toDateString();
                break;
            case 'ayer':
                $ayer = $hoy->copy()->subDay();
                $this->fechaDesde = $ayer->toDateString();
                $this->fechaHasta = $ayer->toDateString();
                break;
            case 'semana_actual':
                $this->fechaDesde = $hoy->startOfWeek()->toDateString();
                $this->fechaHasta = $hoy->endOfWeek()->toDateString();
                break;
            case 'semana_anterior':
                $start = $hoy->copy()->subWeek()->startOfWeek();
                $end = $hoy->copy()->subWeek()->endOfWeek();
                $this->fechaDesde = $start->toDateString();
                $this->fechaHasta = $end->toDateString();
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = $hoy->copy()->subWeeks(2)->startOfWeek()->toDateString();
                $this->fechaHasta = $hoy->endOfWeek()->toDateString();
                break;
            case 'mes_actual':
                $this->fechaDesde = $hoy->startOfMonth()->toDateString();
                $this->fechaHasta = $hoy->endOfMonth()->toDateString();
                break;
            case 'mes_anterior':
                $this->fechaDesde = $hoy->subMonth()->startOfMonth()->toDateString();
                $this->fechaHasta = $hoy->copy()->endOfMonth()->toDateString();
                break;
            case 'personalizado':
            default:
                // No se tocan las fechas
                break;
        }

        $this->resetPage();
    }

    public function validarFechas()
    {
        $this->fechasValidas = true;

        if ($this->fechaDesde || $this->fechaHasta) {
            try {
                $this->validate([
                    'fechaDesde' => 'required_with:fechaHasta|date|before_or_equal:fechaHasta',
                    'fechaHasta' => 'required_with:fechaDesde|date|after_or_equal:fechaDesde'
                ], [
                    'fechaDesde.required_with' => 'La fecha Desde es requerida cuando se especifica Hasta',
                    'fechaHasta.required_with' => 'La fecha Hasta es requerida cuando se especifica Desde',
                    'fechaDesde.before_or_equal' => 'La fecha Desde debe ser anterior o igual a la fecha Hasta',
                    'fechaHasta.after_or_equal' => 'La fecha Hasta debe ser posterior o igual a la fecha Desde'
                ]);
            } catch (ValidationException $e) {
                $this->fechasValidas = false;
                throw $e;
            }
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['fechaDesde', 'fechaHasta']);
        $this->aplicarPeriodo();
        $this->fechasValidas = true;
        $this->resetPage();
    }

    protected function getFooter(): ?\Illuminate\View\View
    {
        $records = $this->getFilteredTableQuery()->get();

        $totalIngresos = $records
        ->where('tipo_concepto', 'Ingresos')
        ->sum(function ($item) {
            return abs($item->monto);
        });

        $totalGastos = $records
            ->where('tipo_concepto', 'Gastos')
            ->sum(function ($item) {
                return abs($item->monto);
            });

        return view('filament.resources.vista-movimiento-resource.resumen', [
            'totalIngresos' => $totalIngresos,
            'totalGastos' => $totalGastos,
        ]);
    }

    public function updated($name)
    {
        if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            if ($name === 'periodoSeleccionado') {
                $this->aplicarPeriodo();
            }

            $this->resetPage();
        }
    }

}

<?php

namespace App\Filament\Resources\AbonosResource\Pages;

use App\Filament\Resources\AbonosResource;
use App\Models\Clientes;
use App\Models\Rutas;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\Ruta;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Livewire\Component as LivewireComponent;

class ListAbonos extends ListRecords
{
    protected static string $resource = AbonosResource::class;

    public int|string|null $clienteId = null;
    public int|string|null $rutaId = null;
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy'; // Cambiado a 'hoy' por defecto

    protected $queryString = [
        'clienteId' => ['except' => null],
        'rutaId' => ['except' => null],
        'fechaDesde' => ['except' => null],
        'fechaHasta' => ['except' => null],
        'periodoSeleccionado' => ['except' => 'hoy'],
    ];

    // 'goToActionRecord' es el evento emitido desde los botones del modal.
    protected $listeners = ['goToActionRecord'];

    public function mount(): void
    {
        parent::mount();

        // Establecer fechas del día actual si no hay filtros aplicados
        if (is_null($this->fechaDesde)) {
            $this->aplicarPeriodo(); // Esto establecerá automáticamente el rango del día actual
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Abono')
                ->button()
                ->color('primary')
                ->url(function () {
                    if (!$this->clienteId) {
                        return '#';
                    }

                    $tieneCreditos = Creditos::where('id_cliente', $this->clienteId)
                        ->where('saldo_actual', '>', 0)
                        ->exists();

                    if (!$tieneCreditos) {
                        $this->notify('warning', 'El cliente no tiene créditos activos');
                        return '#';
                    }

                    return AbonosResource::getUrl('create', ['cliente_id' => $this->clienteId]);
                })
                ->visible($this->clienteId !== null),
        ];
    }

    public function aplicarPeriodo()
    {
        $hoy = Carbon::today();

        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = $hoy->format('Y-m-d');
                $this->fechaHasta = $hoy->format('Y-m-d');
                break;
            case 'ayer':
                $this->fechaDesde = $hoy->subDay()->format('Y-m-d');
                $this->fechaHasta = $this->fechaDesde;
                break;
            case 'semana_actual':
                $this->fechaDesde = $hoy->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->endOfWeek()->format('Y-m-d');
                break;
            case 'semana_anterior':
                $this->fechaDesde = $hoy->subWeek()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->endOfWeek()->format('Y-m-d');
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = $hoy->subWeeks(2)->format('Y-m-d');
                $this->fechaHasta = $hoy->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaDesde = $hoy->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fechaDesde = $hoy->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->endOfMonth()->format('Y-m-d');
                break;
            default:
                // Para 'personalizado' no hacemos nada
                break;
        }

        $this->aplicarFiltroFecha();
    }

    public function aplicarFiltroFecha()
    {
        $this->resetPage();
    }

    public function resetFechas()
    {
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->periodoSeleccionado = 'hoy';
        $this->aplicarPeriodo();
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->with(['cliente', 'credito', 'usuario', 'ruta']);

        // Filtrar por cliente si se ha seleccionado
        if (!empty($this->clienteId)) {
            $query->where('id_cliente', $this->clienteId);
        }

        // Filtrar por ruta si se ha seleccionado
        if (!empty($this->rutaId)) {
            $query->whereHas('cliente', function($q) {
                $q->where('id_ruta', $this->rutaId);
            });
        }

        // Aplicar filtros de fecha
        if ($this->fechaDesde && $this->fechaHasta) {
            $query->whereDate('fecha_pago', '>=', $this->fechaDesde)
                ->whereDate('fecha_pago', '<=', $this->fechaHasta);
        } elseif ($this->fechaDesde) {
            $query->whereDate('fecha_pago', '>=', $this->fechaDesde);
        } elseif ($this->fechaHasta) {
            $query->whereDate('fecha_pago', '<=', $this->fechaHasta);
        } else {
            $hoy = Carbon::today()->format('Y-m-d');
            $query->whereDate('fecha_pago', $hoy);
        }

        return $query->orderBy('fecha_pago', 'desc');
    }

    protected function getHeader(): View
    {
        return view('filament.resources.abonos-resource.header', [
            'clientes' => Clientes::where('activo', true)->get()->pluck('nombre_completo', 'id_cliente'),
            'rutas' => Ruta::all()->pluck('nombre', 'id_ruta'),
            'clienteId' => $this->clienteId,
            'cliente' => $this->clienteId ? Clientes::with(['creditos', 'abonos'])->find($this->clienteId) : null,
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['clienteId', 'rutaId', 'fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            $this->resetPage();
        }
    }


    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    /**
     * Listener para el evento 'goToActionRecord' emitido por los botones de navegación del modal.
     * Este método se define en el componente de página (ListAbonos) porque es el componente padre
     * que controla el modal de la acción de tabla.
     * @param string|int|null $abonoId El ID del abono al que navegar.
     */
    public function goToActionRecord(?string $abonoId): void
    {
        if ($abonoId === 'null' || !is_numeric($abonoId)) {
            return;
        }

        $newRecord = Abonos::find((int) $abonoId);

        if ($newRecord) {

            $this->mountedTableActionRecord = $newRecord->getKey();
        }
    }
}

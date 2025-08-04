<?php

namespace App\Filament\Resources\AbonosResource\Pages;

use App\Exports\AbonosExport;
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
use Maatwebsite\Excel\Facades\Excel;

class ListAbonos extends ListRecords
{
    protected static string $resource = AbonosResource::class;

    public int|string|null $clienteId = null;
    public int|string|null $rutaId = null;
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy'; // Cambiado a 'hoy' por defecto
    public ?string $tipoConcepto = null;


    protected $queryString = [
        'clienteId' => ['except' => null],
        'rutaId' => ['except' => null],
        'fechaDesde' => ['except' => null],
        'fechaHasta' => ['except' => null],
        'periodoSeleccionado' => ['except' => 'hoy'],
        'tipoConcepto' => ['except' => null],
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

                    $tieneCreditos = \App\Models\Creditos::where('id_cliente', $this->clienteId)
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
                $this->fechaDesde = $hoy->copy()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->format('Y-m-d');
                break;
            case 'ayer':
                $this->fechaDesde = $hoy->copy()->subDay()->format('Y-m-d');
                $this->fechaHasta = $this->fechaDesde;
                break;
            case 'semana_actual':
                $this->fechaDesde = $hoy->copy()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'semana_anterior':
                $this->fechaDesde = $hoy->copy()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = $hoy->copy()->subWeeks(2)->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaDesde = $hoy->copy()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fechaDesde = $hoy->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaHasta = $hoy->copy()->subMonth()->endOfMonth()->format('Y-m-d');
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

        if (!empty($this->clienteId)) {
            $query->where('id_cliente', $this->clienteId);
        }

        $rutaId = session('selected_ruta_id');
        if (!empty($rutaId)) {
            $query->whereHas('cliente', function($q) use ($rutaId) {
                $q->where('id_ruta', $rutaId);
            });
        }

        // Filtro por tipo de concepto (Yape - Efectivo)
        if (!empty($this->tipoConcepto)) {
            $query->whereHas('conceptosabonos', function($q) {
                $q->where('tipo_concepto', $this->tipoConcepto);
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

        $rutaId = session('selected_ruta_id');

        $clientesQuery = \App\Models\Clientes::where('activo', true);

        if ($rutaId) {
            $clientesQuery->where('id_ruta', $rutaId);
        }

        return view('filament.resources.abonos-resource.header', [
            'clientes' => $clientesQuery->get()->pluck('nombre_completo', 'id_cliente'),
            'rutas' => Ruta::all()->pluck('nombre', 'id_ruta'),
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'clienteId' => $this->clienteId,
            'cliente' => $this->clienteId ? Clientes::with(['creditos', 'abonos'])->find($this->clienteId) : null,
            'tipoConcepto' => $this->tipoConcepto,
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\AbonosResource\Widgets\AbonosFooter::class

        ];
    }

    public function updated($property)
    {
        if (in_array($property, ['clienteId', 'rutaId', 'fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            $this->resetPage();
        }
        if ($property === 'rutaId') {
            $this->clienteId = null;
        }
    }
        protected function getFooterWidgetsColumns(): int|array
    {
        return 1; // Esto hará que el widget ocupe todo el ancho
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

    public function aplicarRango()
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            $this->periodoSeleccionado = 'personalizado';
            $this->resetPage(); // Aplicar directamente la paginación
        }
    }
    public function exportExcel()
    {
        $query = $this->getTableQuery();

        // Verifica que la consulta tenga resultados
        if ($query->count() === 0) {
            $this->notify('warning', 'No hay datos para exportar');
            return;
        }

        $filename = 'abonos_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new AbonosExport($query), $filename);
    }

}
<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use App\Models\Clientes;
use App\Models\Creditos;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CreditosExcelExport;

class ListCreditos extends ListRecords
{
    protected static string $resource = CreditosResource::class;

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    protected $listeners = ['globalRouteChanged' => 'applyRouteFilter'];

    public ?int $clienteId = null;
    public bool $mostrarSoloActivos = true; // Nueva propiedad para controlar el filtro

    // Propiedades para filtro de fechas
    public $fechaDesde;
    public $fechaHasta;
    public $fechaPeriodo = 'hoy';

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

        // Cambiar el comportamiento: cuando hay un cliente seleccionado, mostrar todos los créditos por defecto
        $this->mostrarSoloActivos = Session::get('creditos_mostrar_solo_activos', false); // Cambiar de true a false

        // Inicializar filtros de fecha
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
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->fechaPeriodo = 'hoy';
        $this->clienteId = null; // Limpiar también la selección del cliente
        $this->aplicarPeriodoFecha('hoy');
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';

        $this->resetPage();
    }

    // Método para alternar el filtro de activos
    public function toggleMostrarSoloActivos(): void
    {
        $this->mostrarSoloActivos = !$this->mostrarSoloActivos;
        Session::put('creditos_mostrar_solo_activos', $this->mostrarSoloActivos);
        $this->resetPage();
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // Opcional: Si quieres mostrar el nombre de la ruta actual en la cabecera de la tabla
    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Créditos (Ruta: " . $this->currentRutaName . ")";
        }
        return "Listado de Créditos";
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // MANTENER EAGER LOADING para evitar N+1 queries
        $query->with(['cliente', 'ruta', 'tipoPago', 'abonos', 'conceptosCredito']);

        $query->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente');

        // Filtrar por ruta si está seleccionada
        if ($this->currentRutaId) {
            $query->where('clientes.id_ruta', $this->currentRutaId);
        }

        $query->where('clientes.activo', true);

        // ✅ NUEVA LÓGICA:
        // Si hay cliente seleccionado: mostrar solo sus créditos SIN filtro de fecha
        // Si NO hay cliente: mostrar todos los créditos CON filtro de fecha
        if ($this->clienteId) {
            // Cliente seleccionado: mostrar solo sus créditos (sin filtro de fecha)
            $query->where('creditos.id_cliente', $this->clienteId);
        } else {
            // Sin cliente seleccionado: aplicar filtros de fecha para todos los créditos
            if ($this->fechaDesde) {
                $query->whereDate('creditos.fecha_credito', '>=', $this->fechaDesde);
            }

            if ($this->fechaHasta) {
                $query->whereDate('creditos.fecha_credito', '<=', $this->fechaHasta);
            }
        }

        // Aplicar filtro de activos si está habilitado
        if ($this->mostrarSoloActivos) {
            $query->where('creditos.saldo_actual', '>', 0);
        }

        $query->orderBy('creditos.fecha_credito', 'desc')
              ->orderBy('creditos.created_at', 'desc');
        $query->select('creditos.*');

        return $query;
    }

    protected function getHeader(): View
    {
        $selectedRutaId = session('selected_ruta_id');

        $clientesQuery = Clientes::where('activo', true);

        if ($selectedRutaId) {
            $clientesQuery->where('id_ruta', $selectedRutaId);
        }

        return view('filament.resources.creditos-resource.header', [
            'clientes' => $clientesQuery->get()->pluck('nombre_completo', 'id_cliente'),
            'clienteId' => $this->clienteId,
            'cliente' => $this->clienteId ? Clientes::with('creditos')->find($this->clienteId) : null,
            'mostrarSoloActivos' => $this->mostrarSoloActivos, // Pasar el estado al header
            'fechaPeriodo' => $this->fechaPeriodo,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
        ]);
    }

    public function exportarPDF()
    {
        $creditos = $this->getTableQuery()->get();

        if ($creditos->isEmpty()) {
            $this->notify('warning', 'No hay créditos para exportar con los filtros aplicados.');
            return;
        }

        try {
            // ✅ USAR EXACTAMENTE LOS MISMOS DATOS DE LA TABLA
            // Crear exportación usando los datos ya filtrados de la tabla
            $export = new \App\Exports\CreditosExport();
            $export->setFilteredData($creditos);

            // Generar y descargar PDF
            return $export->exportToPDF();

        } catch (\Exception $e) {
            $this->notify('danger', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }


    public function updated($property)
    {
        if ($property === 'clienteId') {
            $this->clienteId = $this->clienteId ? (int) $this->clienteId : null;
            // Cuando se selecciona un cliente, automáticamente mostrar todos los créditos
            if ($this->clienteId) {
                $this->mostrarSoloActivos = false;
                Session::put('creditos_mostrar_solo_activos', false);
            }
            $this->resetPage();
        }
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableColumnToggleInSession(): bool
    {
        return false; // Desactivar persistencia de estado de columnas toggleables
    }

    public function isTableVisible(): bool
    {
        // La tabla siempre es visible:
        // - Con cliente seleccionado: muestra sus créditos
        // - Sin cliente: muestra créditos filtrados por fecha
        return true;
    }

    public function exportExcel()
    {
        $query = $this->getTableQuery();

        // Verifica que la consulta tenga resultados
        if ($query->count() === 0) {
            $this->notify('warning', 'No hay datos para exportar');
            return;
        }

        $filename = 'creditos_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new CreditosExcelExport($query), $filename);
    }

    /**
     * Redirige a la página de historial completo del cliente
     */
    public function verHistorialCliente()
    {
        if (!$this->clienteId) {
            $this->notify('warning', 'Debe seleccionar un cliente primero.');
            return;
        }

        // Buscar el cliente para obtener su información
        $cliente = Clientes::find($this->clienteId);
        
        if (!$cliente) {
            $this->notify('danger', 'Cliente no encontrado.');
            return;
        }

        // Usar el método de navegación de Livewire
        $this->redirect(url("/creditos/historial-cliente/{$this->clienteId}"));
    }

    /**
     * Redirige al historial del crédito activo del cliente
     */
    public function verCreditoActivo()
    {
        if (!$this->clienteId) {
            $this->notify('warning', 'Debe seleccionar un cliente primero.');
            return;
        }

        // Buscar el crédito activo del cliente (con saldo > 0)
        $creditoActivo = Creditos::where('id_cliente', $this->clienteId)
            ->where('saldo_actual', '>', 0)
            ->orderBy('fecha_credito', 'desc')
            ->first();

        // Si no hay crédito activo, buscar el último crédito del cliente
        if (!$creditoActivo) {
            $creditoActivo = Creditos::where('id_cliente', $this->clienteId)
                ->orderBy('fecha_credito', 'desc')
                ->first();
        }

        if (!$creditoActivo) {
            $this->notify('warning', 'Este cliente no tiene créditos registrados.');
            return;
        }

        // Redirigir a la página de vista del crédito
        return redirect()->route('filament.resources.creditos.view', ['record' => $creditoActivo->id_credito]);
    }
}

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
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListCreditos extends ListRecords
{
    protected static string $resource = CreditosResource::class;

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'eliminarCredito' => 'eliminarCredito'
    ];

    public ?int $clienteId = null;
    public bool $mostrarSoloActivos = true; // Nueva propiedad para controlar el filtro

    // Propiedades para filtro de fechas
    public $fechaDesde;
    public $fechaHasta;
    public $fechaPeriodo = 'hoy';

    public function mount(): void
    {
        parent::mount();

        $clienteIdFromQuery = request()->query('cliente_id');
        if ($clienteIdFromQuery) {
            $parsedId = (int) $clienteIdFromQuery;
            if ($parsedId > 0) {
                \Illuminate\Support\Facades\Session::put('creditos_cliente_id', $parsedId);
                \Illuminate\Support\Facades\Session::put('creditos_mostrar_solo_activos', false);
            }
        }

        if (Session::has('selected_ruta_id')) {
            $this->currentRutaId = Session::get('selected_ruta_id');
            $this->currentRutaName = Session::get('selected_ruta_name');
        } else {
            $this->currentRutaId = null;
            $this->currentRutaName = 'Ruta';
        }

        // Validar que la ruta en sesión pertenezca al usuario actual
        $user = auth()->user();
        if ($user && $this->currentRutaId) {
            $hasAccess = false;
            if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                $hasAccess = true;
            } else {
                $hasAccess = $user->rutas()->where('ruta.id_ruta', $this->currentRutaId)->exists();
            }

            if (!$hasAccess) {
                // Limpiar sesión y estado local si la ruta no es accesible
                Session::put('selected_ruta_id', null);
                Session::put('selected_ruta_name', 'Ruta');
                $this->currentRutaId = null;
                $this->currentRutaName = 'Ruta';

                // Fallback a la última ruta seleccionada por el usuario si es válida
                $fallbackRuta = null;
                if (!empty($user->last_selected_ruta_id)) {
                    $candidate = \App\Models\Ruta::find($user->last_selected_ruta_id);
                    if ($candidate) {
                        if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                            $fallbackRuta = $candidate;
                        } else {
                            $hasUserAccess = $user->rutas()->where('ruta.id_ruta', $candidate->id_ruta)->exists();
                            if ($hasUserAccess) {
                                $fallbackRuta = $candidate;
                            }
                        }
                    }
                }

                if ($fallbackRuta) {
                    Session::put('selected_ruta_id', $fallbackRuta->id_ruta);
                    Session::put('selected_ruta_name', $fallbackRuta->nombre_completo ?? $fallbackRuta->nombre);
                    $this->currentRutaId = $fallbackRuta->id_ruta;
                    $this->currentRutaName = $fallbackRuta->nombre_completo ?? $fallbackRuta->nombre;
                    // Reiniciar la paginación para reflejar el cambio
                    $this->resetPage();
                }
            }
        }

        // Restaurar el cliente seleccionado desde sesión si existe (una sola vez)
        $persistedClienteId = Session::pull('creditos_cliente_id');
        if ($persistedClienteId && empty($this->clienteId)) {
            // Verificar que el cliente pertenezca a la ruta actual antes de restaurarlo
            if ($this->currentRutaId) {
                $cliente = \App\Models\Clientes::where('id_cliente', $persistedClienteId)
                    ->where('id_ruta', $this->currentRutaId)
                    ->first();
                
                if ($cliente) {
                    $this->clienteId = (int) $persistedClienteId;
                    // Al tener cliente, mostrar todos los créditos (no solo activos)
                    $this->mostrarSoloActivos = false;
                    Session::put('creditos_mostrar_solo_activos', false);
                }
            }
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
        \Illuminate\Support\Facades\Session::forget('creditos_cliente_id');
        $this->aplicarPeriodoFecha('hoy');
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        Log::info('ListCreditos: applyRouteFilter ejecutado', [
            'rutaId' => $rutaId,
            'rutaName' => $rutaName,
            'clienteId_antes' => $this->clienteId
        ]);

        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';

        // Al cambiar de ruta, limpiar la selección de cliente para evitar mostrar datos de otra ruta
        if ($this->clienteId !== null) {
            $this->clienteId = null;
        }
        Session::forget('creditos_cliente_id');

        Log::info('ListCreditos: Cliente limpiado', [
            'clienteId_despues' => $this->clienteId
        ]);

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

        $clientesQuery = Clientes::where('activo', true)
            ->with(['creditos' => function ($q) {
                $q->where('saldo_actual', '>', 0);
            }]);

        if ($selectedRutaId) {
            $clientesQuery->where('id_ruta', $selectedRutaId);
        }

        $clientesLista = $clientesQuery->get();
        $clientes = $clientesLista->pluck('nombre_completo', 'id_cliente');
        $clientesActivos = $clientesLista->mapWithKeys(function ($c) {
            return [$c->id_cliente => $c->creditos->isNotEmpty()];
        });

        return view('filament.resources.creditos-resource.header', [
            'clientes' => $clientes,
            'clientesActivos' => $clientesActivos,
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
                // Persistir selección del cliente para restaurarla al reingresar
                Session::put('creditos_cliente_id', $this->clienteId);
            } else {
                Session::forget('creditos_cliente_id');
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

        // Persistir temporalmente el cliente seleccionado para restaurarlo al volver
        Session::put('creditos_cliente_id', $this->clienteId);

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

    /**
     * Eliminar crédito sin abonos
     */
    public function eliminarCredito($creditoId)
    {
        try {
            $credito = Creditos::findOrFail($creditoId);
            
            // Verificar que el crédito no tenga abonos
            if ($credito->abonos()->exists()) {
                Notification::make()
                    ->title('No se puede eliminar el crédito')
                    ->body('Este crédito tiene abonos realizados y no puede ser eliminado.')
                    ->danger()
                    ->send();
                return;
            }

            // Eliminar el YapeCliente asociado si existe
            if ($credito->yapeCliente) {
                $credito->yapeCliente->forceDelete();
            }

            $clienteNombre = $credito->cliente?->nombre . ' ' . $credito->cliente?->apellido;
            $rutaNombre = $credito->cliente?->ruta?->nombre ?? 'Ruta desconocida';

            // Registrar en el log de actividad
            \App\Models\LogActividad::registrar(
                'Créditos',
                "Eliminó el crédito de {$clienteNombre} de la ruta {$rutaNombre}",
                [
                    'credito_id' => $credito->id_credito,
                    'cliente_id' => $credito->id_cliente,
                    'datos_eliminados' => $credito->toArray(),
                ]
            );

            // Eliminar el crédito
            $credito->delete();

            Notification::make()
                ->title('Crédito eliminado')
                ->body('El crédito ha sido eliminado correctamente.')
                ->success()
                ->send();

            // Refrescar la vista
            $this->resetTable();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al eliminar crédito')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Vista personalizada para el listado con diseño responsivo
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'records' => $this->getTableRecords(),
        ]);
    }

    protected static string $view = 'filament.resources.creditos-resource.list-creditos-responsive';

    /**
     * Opciones de paginación para el listado de créditos
     */
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [-1 => 'Todos', 10, 25, 50, 100];
    }

    /**
     * Opción por defecto de registros por página
     */
    protected function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return -1; // -1 representa "todos" en Filament
    }
}

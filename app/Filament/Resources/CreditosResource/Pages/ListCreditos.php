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

class ListCreditos extends ListRecords
{
    protected static string $resource = CreditosResource::class;

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    protected $listeners = ['globalRouteChanged' => 'applyRouteFilter'];

    public ?int $clienteId = null;
    public bool $mostrarSoloActivos = true; // Nueva propiedad para controlar el filtro

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
        // ✅ NUEVA LÓGICA: Solo mostrar créditos cuando hay cliente seleccionado

        // Si NO hay cliente seleccionado, mostrar tabla vacía
        if (!$this->clienteId) {
            return parent::getTableQuery()->whereRaw('1 = 0'); // Tabla vacía por defecto
        }

        // ✅ SOLO cuando hay cliente seleccionado, mostrar sus créditos
        $query = parent::getTableQuery();

        // MANTENER EAGER LOADING para evitar N+1 queries
        $query->with(['cliente', 'ruta', 'tipoPago', 'abonos', 'conceptosCredito']);

        $query->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente');

        // Filtrar por ruta si está seleccionada
        if ($this->currentRutaId) {
            $query->where('clientes.id_ruta', $this->currentRutaId);
        }

        $query->where('clientes.activo', true);

        // ✅ FILTRAR POR CLIENTE ESPECÍFICO (requerido)
        $query->where('creditos.id_cliente', $this->clienteId);

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
        ]);
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

    public function isTableVisible(): bool
    {
        return $this->clienteId !== null;
    }
}

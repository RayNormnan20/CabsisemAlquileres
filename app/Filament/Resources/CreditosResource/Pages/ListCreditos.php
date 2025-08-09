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
        // Si no hay cliente seleccionado, retornar consulta vacía
        if (!$this->clienteId) {
            return parent::getTableQuery()->whereRaw('1 = 0'); // Consulta que no retorna resultados
        }

        $query = parent::getTableQuery();

        $query->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente');

        if ($this->currentRutaId) {
            $query->where('clientes.id_ruta', $this->currentRutaId);
        }

        $query->where('clientes.activo', true);

        if ($this->clienteId) {
            $query->where('creditos.id_cliente', $this->clienteId);
            // Cuando se selecciona un cliente específico, mostrar todos los créditos (activos y pagados)
            // No aplicar el filtro de solo activos automáticamente
        }

        // Solo aplicar filtro de créditos activos si está explícitamente habilitado Y no hay cliente específico seleccionado
        // O si el usuario ha activado manualmente el filtro
        if ($this->mostrarSoloActivos && !$this->clienteId) {
            $query->where('creditos.saldo_actual', '>', 0);
        } elseif ($this->mostrarSoloActivos && $this->clienteId) {
            // Si hay cliente seleccionado y el usuario quiere ver solo activos, aplicar el filtro
            $query->where('creditos.saldo_actual', '>', 0);
        }

        $query->orderBy('creditos.fecha_credito', 'desc');

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
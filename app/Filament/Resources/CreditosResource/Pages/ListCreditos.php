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
    }
   
    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';

        
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

    /* protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // 2. Aplica el filtro por RUTA (si hay una ruta seleccionada)
        // Esto asegura que siempre se filtren por la ruta si está activa
        if ($this->currentRutaId) {
            $query->where('id_ruta', $this->currentRutaId);
        }

        if ($this->clienteId) {
            $query->where('id_cliente', $this->clienteId);
        }
        
        // 4. Aplica el ordenamiento
        $query->orderBy('fecha_credito', 'desc');

        return $query;
    } */
    /* protected function getTableQuery(): Builder
    {
        // Start with the base query for Creditos
        $query = parent::getTableQuery();

        // 1. Join with the 'clientes' table to access client-specific properties
        $query->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente')
            ->where('clientes.activo', true); // Filter for active clients

        // 2. Apply the route filter if a route is selected in the session
        // This ensures only credits for clients in the selected route are shown
        if ($this->currentRutaId) {
            $query->where('clientes.id_ruta', $this->currentRutaId);
        }

        // 3. Apply the specific client filter if 'clienteId' is set
        // This will filter credits for a specific client within the selected route
        if ($this->clienteId) {
            $query->where('creditos.id_cliente', $this->clienteId);
        }

        // 4. Order the results
        $query->orderBy('creditos.fecha_credito', 'desc');

        // Make sure to select creditos.* to avoid column name conflicts
        // if there are columns with the same name in 'clientes' and 'creditos'
        $query->select('creditos.*');

        return $query;
    } */
   
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $query->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente');

        if ($this->currentRutaId) {
            $query->where('clientes.id_ruta', $this->currentRutaId);
        }

        $query->where('clientes.activo', true);

        if ($this->clienteId) {
            $query->where('creditos.id_cliente', $this->clienteId);
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
        ]);
    }

    public function updated($property)
    {
        if ($property === 'clienteId') {
            $this->clienteId = $this->clienteId ? (int) $this->clienteId : null;
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
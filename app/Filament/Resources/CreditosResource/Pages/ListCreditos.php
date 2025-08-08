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

        // Mantener el estado del filtro en la sesión
        $this->mostrarSoloActivos = Session::get('creditos_mostrar_solo_activos', true);
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
        }

        // Aplicar filtro de créditos activos si está habilitado
        if ($this->mostrarSoloActivos) {
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
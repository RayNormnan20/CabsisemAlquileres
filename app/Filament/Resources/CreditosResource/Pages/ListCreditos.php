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

    protected function getTableQuery(): Builder
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
    }
   

       protected function getHeader(): View
        {
            return view('filament.resources.creditos-resource.header', [
                'clientes' => Clientes::where('activo', true)->get()->pluck('nombre_completo', 'id_cliente'),
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
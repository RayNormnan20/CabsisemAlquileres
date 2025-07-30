<?php

namespace App\Http\Livewire;

use App\Models\Ruta;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SelectedRouteManager extends Component
{   
    
    public ?int $selectedRutaId = null;
    public ?string $selectedRutaName = null; 

   
    protected $listeners = [
        'routeSelected' => 'onRouteSelected',
        
    ];

    public function mount()
    {
        $this->selectedRutaId = session('selected_ruta_id');
        $this->selectedRutaName = session('selected_ruta_name', 'Ruta');

        // Emitir evento si hay una ruta seleccionada en sesión
        if ($this->selectedRutaId && $this->selectedRutaName && $this->selectedRutaName !== 'Ruta') {
            $this->emit('globalRouteChanged', $this->selectedRutaId, $this->selectedRutaName);
        }
    }

    public function onRouteSelected($data)
    {
        $this->selectedRutaId = $data['id'];
        $this->selectedRutaName = $data['name'];

        session([
            'selected_ruta_id' => $data['id'],
            'selected_ruta_name' => $data['name'],
        ]);
        $this->emit('globalRouteChanged', $data['id'], $data['name']);
    }

    // Método para limpiar la ruta seleccionada
    public function clearSelectedRoute()
    {
        $this->selectedRutaId = null;
        $this->selectedRutaName = 'Ruta'; 
        session([
            'selected_ruta_id' => null,
            'selected_ruta_name' => 'Ruta',
        ]);
        $this->emit('globalRouteChanged', null, 'Ruta');
    }

    public function render()
    {
        return view('livewire.selected-route-manager');
    }
}

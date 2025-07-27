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
        
        if (Session::has('selected_ruta_id')) {
            $id = Session::get('selected_ruta_id');
            $name = Session::get('selected_ruta_name');
            
            // Opcional: Verifica que la ruta todavía exista y sea activa si es necesario
            // $ruta = Ruta::find($id); // O Ruta::activas()->find($id);
            // if ($ruta && ($ruta->nombre_completo ?? $ruta->nombre) === $name) { 
            //     $this->selectedRutaId = $ruta->id_ruta;
            //     $this->selectedRutaName = $name;
            // } else {
            //     // Si la ruta no es válida o el nombre ha cambiado, la limpiamos de la sesión
            //     $this->clearSelectedRoute();
            // }

            
            $this->selectedRutaId = $id;
            $this->selectedRutaName = $name;
        } else {
            // Si no hay nada en sesión, asegúrate de que esté vacío (o un valor predeterminado)
            $this->selectedRutaId = null;
            $this->selectedRutaName = 'Ruta'; // O el texto predeterminado inicial del botón
        }
    }

     // Este método se llama cuando el evento 'routeSelected' es emitido
    public function onRouteSelected(int $rutaId, string $rutaName)
    {
        $this->selectedRutaId = $rutaId;
        $this->selectedRutaName = $rutaName;

        
        Session::put('selected_ruta_id', $rutaId);
        Session::put('selected_ruta_name', $rutaName);

        
        $this->emit('globalRouteChanged', $this->selectedRutaId, $this->selectedRutaName);
    }

    // Método para limpiar la ruta seleccionada
    public function clearSelectedRoute()
    {
        $this->selectedRutaId = null;
        $this->selectedRutaName = 'Ruta'; 
        Session::forget('selected_ruta_id');
        Session::forget('selected_ruta_name');
        $this->emit('globalRouteChanged', null, 'Ruta');
    }
    
    public function render()
    {
        return view('livewire.selected-route-manager');
    }
}

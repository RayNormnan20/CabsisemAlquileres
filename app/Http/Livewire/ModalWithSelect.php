<?php

namespace App\Http\Livewire;

use App\Models\Ruta;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use PhpParser\ErrorHandler\Collecting;

class ModalWithSelect extends Component
{   
    public $selectedOption = '';
    public $showModal = false;
    public $routeButtonComponentId;

    public Collection $rutas;

    public function mount()
    {
        $this->rutas = Ruta::activas()->get(); 

        if ($this->rutas->isNotEmpty()) {
            // Opcional: Si quieres que el botón muestre la primera ruta activa por defecto al cargar el componente,
            // descomenta la siguiente línea y asegúrate de que RouteButton tenga una lógica de mount para esto
            // $this->selectedOption = $this->rutas->first()->id_ruta;
            // $this->updatedSelectedOption($this->selectedOption); // Para actualizar el botón inmediatamente
        }
    }


    public function render()
    {
        return view('livewire.modal-with-select', [
            'rutas' => $this->rutas,
        ]);
    }
    
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-con-select']);
    }


    public function updatedSelectedOption($value)
    {   
        $selectedRutaId = null;
        $newLabel = 'Ruta';


        if (!empty($value)) {
            $selectedRuta = $this->rutas->firstWhere('id_ruta', $value);
            if ($selectedRuta) {
                $selectedRutaId = $selectedRuta->id_ruta;
                // Usa el atributo accesorio 'nombre_completo' si lo definiste
                $newLabel = $selectedRuta->nombre_completo ?? $selectedRuta->nombre;
            }
        }

        $this->emitTo('route-button', 'optionSelectedInModal', $newLabel);
        
        $this->emit('routeSelected', $selectedRutaId, $newLabel); 

        Notification::make()
            ->title('Opción seleccionada: ' . $newLabel)
            ->success()
            ->send();

        if ($this->routeButtonComponentId) {
            $this->emitTo('route-button', 'optionSelectedInModal', $newLabel);
        } else {

            $this->emit('optionSelectedInModal', $newLabel);
        }

        $this->closeModal(); 
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\Ruta;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ModalWithSelect extends Component
{   
    public $selectedOption = '';
    public $showModal = false;
    public $routeButtonComponentId;

    public Collection $rutas;

    public function mount()
    {
        $user = auth()->user();

        $this->rutas = $user->rutas()->where('activa', true)->get();
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
    

    public function confirmSelection()
    {
        if (!$this->selectedOption) {
            return;
        }

        $ruta = Ruta::find($this->selectedOption);

        if ($ruta) {
            $this->emit('routeSelected', [
                'id' => $ruta->id_ruta,
                'name' => $ruta->nombre_completo ?? $ruta->nombre,
            ]);


            
            session([
                'selected_ruta_id' => $ruta->id_ruta,
                'selected_ruta_name' => $ruta->nombre_completo ?? $ruta->nombre,
            ]);

            Notification::make()
                ->title('Opción seleccionada: ' . ($ruta->nombre_completo ?? $ruta->nombre))
                ->success()
                ->send();

            $this->emitTo('route-button', 'optionSelectedInModal', $ruta->nombre_completo ?? $ruta->nombre);
        }

        $this->closeModal();
    }
    
    /* public function updatedSelectedOption($value)
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
    } */
    
    public function render()
    {
        return view('livewire.modal-with-select', [
            'rutas' => $this->rutas,
        ]);
    }
}

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

            // Persistir la última ruta seleccionada en el usuario
            if ($user = auth()->user()) {
                try {
                    $user->last_selected_ruta_id = $ruta->id_ruta;
                    $user->save();
                } catch (\Throwable $e) {
                    // Silenciar errores para no interrumpir la experiencia del usuario
                }
            }

            Notification::make()
                ->title('Ruta seleccionada: ' . ($ruta->nombre_completo ?? $ruta->nombre))
                ->success()
                ->send();

            $this->emitTo('route-button', 'optionSelectedInModal', $ruta->nombre_completo ?? $ruta->nombre);
        }

        $this->closeModal();
        return redirect()->to(request()->header('Referer'));

    }

    // Al cambiar la opción en el select, ejecutar automáticamente la acción de "Seleccionar"
    public function updatedSelectedOption($value)
    {
        if (!empty($value)) {
            $this->confirmSelection();
        }
    }
    
    public function render()
    {
        return view('livewire.modal-with-select', [
            'rutas' => $this->rutas,
        ]);
    }
}

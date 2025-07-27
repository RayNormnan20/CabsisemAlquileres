<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Session;
use Livewire\Component;

class RouteButton extends Component
{
    // Esta propiedad contendrá el texto que se mostrará en el botón.
    public $buttonText = 'Ruta';

    // Escucha el evento 'optionSelectedInModal' que será emitido por ModalWithSelect
    // Cuando este evento se recibe, llama al método 'updateButtonText'.
    protected $listeners = ['optionSelectedInModal' => 'updateButtonText'];

     // ¡AÑADE ESTE MÉTODO mount()!
    public function mount()
    {
        // Al montar, intenta cargar el nombre de la ruta desde la sesión
        if (Session::has('selected_ruta_name')) {
            $this->buttonText = Session::get('selected_ruta_name');
        } else {
            // Si no hay ruta seleccionada en sesión, usa el valor por defecto
            $this->buttonText = 'Ruta';
        }

        // Opcional: Si quieres que el botón refleje también la selección inicial del SelectedRouteManager
        // Esto es útil si SelectedRouteManager hace validaciones y puede limpiar la sesión.
        // Puedes escuchar el evento global del SelectedRouteManager o leer su estado.
        // Una forma sería:
        // $selectedRutaManager = app(\Livewire\LivewireManager::class)->newInstance('selected-route-manager');
        // $selectedRutaManager->call('mount'); // Forzar mount si no está ya montado
        // if ($selectedRutaManager->selectedRutaName) {
        //     $this->buttonText = $selectedRutaManager->selectedRutaName;
        // }
    }

    // Este método actualiza el texto del botón.
    public function updateButtonText($newLabel)
    {
        $this->buttonText = $newLabel;
    }

    public function render()
    {
        return view('livewire.route-button');
    }
}

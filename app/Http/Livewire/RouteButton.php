<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Session;
use Livewire\Component;

class RouteButton extends Component
{
    public $buttonText = 'Ruta';

    protected $listeners = [
        'optionSelectedInModal' => 'updateButtonText',
        'globalRouteChanged' => 'updateFromSession', // <-- escucha cambios globales
    ];

    public function mount() 
    {
        $this->updateFromSession();
    }

    public function updateButtonText($newLabel)
    {
        $this->buttonText = $newLabel;
    }

    public function updateFromSession()
    {
        $this->buttonText = session('selected_ruta_name', 'Ruta');
    }

    public function render()
    {
        $this->buttonText = session('selected_ruta_name', 'Ruta');
        return view('livewire.route-button');
    }
}
<?php

namespace App\Http\Livewire;

use App\Models\Ruta;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Validar que la ruta en sesión pertenece al usuario actual
        $user = Auth::user();
        if ($user && $this->selectedRutaId) {
            $hasAccess = false;

            if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                $hasAccess = true;
            } else {
                // Verificar que la ruta esté asignada al usuario
                $hasAccess = $user->rutas()->where('ruta.id_ruta', $this->selectedRutaId)->exists();
            }

            if (!$hasAccess) {
                // Limpiar sesión si la ruta no pertenece al usuario
                Session::put('selected_ruta_id', null);
                Session::put('selected_ruta_name', 'Ruta');
                $this->selectedRutaId = null;
                $this->selectedRutaName = 'Ruta';
                // Intentar restaurar la última ruta seleccionada por el usuario si es válida
                $fallbackRuta = null;
                if (!empty($user->last_selected_ruta_id)) {
                    $candidate = Ruta::find($user->last_selected_ruta_id);
                    if ($candidate) {
                        if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                            $fallbackRuta = $candidate;
                        } else {
                            $hasUserAccess = $user->rutas()->where('ruta.id_ruta', $candidate->id_ruta)->exists();
                            if ($hasUserAccess) {
                                $fallbackRuta = $candidate;
                            }
                        }
                    }
                }

                if ($fallbackRuta) {
                    $this->selectedRutaId = $fallbackRuta->id_ruta;
                    $this->selectedRutaName = $fallbackRuta->nombre_completo ?? $fallbackRuta->nombre;
                    Session::put('selected_ruta_id', $this->selectedRutaId);
                    Session::put('selected_ruta_name', $this->selectedRutaName);
                    // Emitir evento para sincronizar con componentes que dependan de la ruta
                    $this->emit('globalRouteChanged', $this->selectedRutaId, $this->selectedRutaName);
                } else {
                    // Emitir evento para que otros componentes sincronicen el estado vacío
                    $this->emit('globalRouteChanged', null, 'Ruta');
                }
            }
        }

        // Emitir evento si hay una ruta seleccionada en sesión
        if ($this->selectedRutaId && $this->selectedRutaName && $this->selectedRutaName !== 'Ruta') {
            $this->emit('globalRouteChanged', $this->selectedRutaId, $this->selectedRutaName);
        }
    }

    public function onRouteSelected($data)
    {
        Log::info('SelectedRouteManager: Ruta seleccionada', [
            'rutaId' => $data['id'],
            'rutaName' => $data['name']
        ]);

        $this->selectedRutaId = $data['id'];
        $this->selectedRutaName = $data['name'];

        session([
            'selected_ruta_id' => $data['id'],
            'selected_ruta_name' => $data['name'],
        ]);
        
        Log::info('SelectedRouteManager: Emitiendo evento globalRouteChanged');
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

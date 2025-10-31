<?php

namespace App\Http\Livewire\Traits;

use App\Models\Ruta;
use Illuminate\Support\Facades\Auth;

trait RouteValidation
{
    /**
     * Valida y corrige la ruta seleccionada en sesión para el usuario actual.
     * Si la ruta en sesión no es válida, intenta usar last_selected_ruta_id del usuario.
     * 
     * @param string $localStateProperty Nombre de la propiedad local para actualizar (ej: 'selectedRuta')
     * @param bool $resetPagination Si debe resetear la paginación cuando cambie la ruta
     * @return bool True si se hizo algún cambio en la ruta
     */
    protected function validateAndCorrectSelectedRoute($localStateProperty = 'selectedRuta', $resetPagination = true)
    {
        $user = Auth::user();
        $sessionRouteId = session('selected_ruta_id');
        $routeChanged = false;

        // Si no hay ruta en sesión, no hay nada que validar
        if (!$sessionRouteId) {
            return false;
        }

        // Verificar si el usuario tiene acceso a la ruta actual en sesión
        $hasAccess = $this->userHasAccessToRoute($user, $sessionRouteId);

        if (!$hasAccess) {
            // Intentar fallback a last_selected_ruta_id del usuario
            $fallbackRouteId = $user->last_selected_ruta_id;
            
            if ($fallbackRouteId && $this->userHasAccessToRoute($user, $fallbackRouteId)) {
                // Usar la ruta de fallback
                $fallbackRoute = Ruta::find($fallbackRouteId);
                if ($fallbackRoute) {
                    session([
                        'selected_ruta_id' => $fallbackRoute->id_ruta,
                        'selected_ruta_name' => $fallbackRoute->nombre
                    ]);
                    
                    // Actualizar propiedad local si existe
                    if (property_exists($this, $localStateProperty)) {
                        $this->$localStateProperty = $fallbackRoute->nombre;
                    }
                    
                    $routeChanged = true;
                }
            } else {
                // No hay fallback válido, limpiar sesión
                session()->forget(['selected_ruta_id', 'selected_ruta_name']);
                
                // Resetear propiedad local si existe
                if (property_exists($this, $localStateProperty)) {
                    $this->$localStateProperty = 'Ruta';
                }
                
                $routeChanged = true;
            }

            // Resetear paginación si se solicita y el método existe
            if ($resetPagination && $routeChanged && method_exists($this, 'resetPage')) {
                $this->resetPage();
            }
        }

        return $routeChanged;
    }

    /**
     * Verifica si un usuario tiene acceso a una ruta específica.
     * 
     * @param \App\Models\User $user
     * @param int $routeId
     * @return bool
     */
    protected function userHasAccessToRoute($user, $routeId)
    {
        // Verificar si la ruta está en las rutas asignadas al usuario
        // Esto aplica para todos los usuarios, incluyendo Super Admin y Administrador
        return $user->rutas()->where('ruta.id_ruta', $routeId)->exists();
    }

    /**
     * Inicializa la ruta seleccionada usando la validación.
     * Útil para llamar en mount() de componentes Livewire.
     * 
     * @param string $localStateProperty
     * @param bool $resetPagination
     */
    protected function initializeSelectedRoute($localStateProperty = 'selectedRuta', $resetPagination = true)
    {
        // Validar y corregir la ruta actual
        $this->validateAndCorrectSelectedRoute($localStateProperty, $resetPagination);
        
        // Si después de la validación aún tenemos una ruta en sesión, actualizar la propiedad local
        if (session('selected_ruta_id') && property_exists($this, $localStateProperty)) {
            $this->$localStateProperty = session('selected_ruta_name', 'Ruta');
        } elseif (property_exists($this, $localStateProperty)) {
            $this->$localStateProperty = 'Ruta';
        }
    }
}
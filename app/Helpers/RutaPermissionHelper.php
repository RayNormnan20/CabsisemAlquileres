<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ruta;

class RutaPermissionHelper
{
    /**
     * Verificar si el usuario está actualmente en una de sus rutas asignadas
     *
     * @return bool
     */
    public static function isUserInAssignedRoute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $selectedRutaId = Session::get('selected_ruta_id');

        // Si no hay ruta seleccionada, no mostrar módulos especiales
        if (!$selectedRutaId) {
            return false;
        }

        // Verificar si el usuario está asignado a la ruta seleccionada
        return $user->rutas()->where('ruta.id_ruta', $selectedRutaId)->exists();
    }

    /**
     * Obtener la ruta actualmente seleccionada
     *
     * @return \App\Models\Ruta|null
     */
    public static function getCurrentSelectedRoute(): ?Ruta
    {
        $selectedRutaId = Session::get('selected_ruta_id');

        if (!$selectedRutaId) {
            return null;
        }

        return Ruta::find($selectedRutaId);
    }

    /**
     * Verificar si el usuario puede ver un módulo específico por estar en su ruta
     *
     * @param string $moduleName
     * @return bool
     */
    public static function canAccessModuleByRoute(string $moduleName): bool
    {
        // Si el usuario no está en una ruta asignada, usar permisos normales
        if (!self::isUserInAssignedRoute()) {
            return false;
        }

        // Obtener la ruta actual del usuario
        $currentRoute = self::getCurrentSelectedRoute();
        if (!$currentRoute) {
            return false;
        }

        // Verificar si el módulo tiene configuración específica de rutas
        $configuracion = DB::table('configuracion_rutas_reportes')
            ->where('modulo', $moduleName)
            ->first();

        if ($configuracion) {
            // Si hay configuración, verificar si la ruta actual está permitida
            $rutasPermitidas = json_decode($configuracion->rutas_permitidas, true) ?? [];
            return in_array($currentRoute->nombre, $rutasPermitidas);
        }

        // Si no hay configuración específica, usar la lista por defecto (solo para ReportesCristian)
        $routeSpecificModules = [
            'ReportesCristian',
            // Solo ReportesCristian por ahora, otros módulos necesitan configuración explícita
        ];

        return in_array($moduleName, $routeSpecificModules);
    }

    /**
     * Verificar si el usuario puede acceder a un módulo (combinando permisos normales y por ruta)
     *
     * @param string $moduleName
     * @param string $permission
     * @return bool
     */
    public static function canAccessModule(string $moduleName, string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Verificar permisos normales primero
        if ($user->can($permission)) {
            return true;
        }

        // Si no tiene permisos normales, verificar si puede acceder por estar en su ruta
        return self::canAccessModuleByRoute($moduleName);
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Helpers\RutaPermissionHelper;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReportesCristianoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the reports.
     * Ahora permite acceso si tiene permisos normales O está en su ruta asignada
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Usar el nuevo sistema de permisos por ruta
        if (RutaPermissionHelper::canAccessModule('ReportesCristian', 'Ver Reportes Cristian')) {
            $message = RutaPermissionHelper::isUserInAssignedRoute()
                ? 'Acceso permitido por estar en tu ruta asignada.'
                : 'Acceso permitido por permisos de rol.';
            return Response::allow($message);
        }

        return Response::deny('No tienes permiso para ver los reportes de Cristian.');
    }


    /**
     * Determine whether the user can access settings related to reports.
     * Ahora permite acceso si tiene permisos normales O está en su ruta asignada
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manageSettings(User $user)
    {
        // Usar el nuevo sistema de permisos por ruta
        if (RutaPermissionHelper::canAccessModule('ReportesCristian', 'Ver Reportes Cristian')) {
            $message = RutaPermissionHelper::isUserInAssignedRoute()
                ? 'Acceso permitido por estar en tu ruta asignada.'
                : 'Acceso permitido por permisos de rol.';
            return Response::allow($message);
        }

        return Response::deny('No tienes permiso para gestionar la configuración de reportes de Cristian.');
    }
}

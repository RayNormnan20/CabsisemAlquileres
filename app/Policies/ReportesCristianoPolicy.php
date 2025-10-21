<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReportesCristianoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the reports.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('Ver Reportes Cristian')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver los reportes de Cristian.');
    }


    /**
     * Determine whether the user can access settings related to reports.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manageSettings(User $user)
    {
        return $user->can('Ver Reportes Cristian')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar la configuración de reportes de Cristian.');
    }
}

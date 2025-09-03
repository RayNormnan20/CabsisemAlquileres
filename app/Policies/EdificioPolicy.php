<?php

namespace App\Policies;

use App\Models\Edificio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EdificioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('Listar Edificios')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar edificios.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Edificio $edificio)
    {
        return $user->can('Ver Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este edificio.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear edificios.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Edificio $edificio)
    {
        return $user->can('Actualizar Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este edificio.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Edificio $edificio)
    {
        return $user->can('Eliminar Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este edificio.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Edificio $edificio)
    {
        return $user->can('Restaurar Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para restaurar este edificio.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Edificio $edificio)
    {
        return $user->can('Eliminar Permanentemente Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este edificio.');
    }

    /**
     * Determine whether the user can toggle building status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Edificio  $edificio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleActive(User $user, Edificio $edificio)
    {
        return $user->can('Cambiar Estado Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para cambiar el estado de este edificio.');
    }

    /**
     * Determine whether the user can assign routes to buildings.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignRoute(User $user)
    {
        return $user->can('Asignar Ruta Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para asignar rutas a edificios.');
    }

    /**
     * Determine whether the user can manage buildings.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user)
    {
        return $user->can('Gestionar Edificios')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar edificios.');
    }

    /**
     * Determine whether the user can view building occupancy.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewOccupancy(User $user)
    {
        return $user->can('Ver Ocupación Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver la ocupación de edificios.');
    }

    /**
     * Determine whether the user can view building reports.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewReports(User $user)
    {
        return $user->can('Ver Reportes Edificio')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver reportes de edificios.');
    }
}
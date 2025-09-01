<?php

namespace App\Policies;

use App\Models\Oficina;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OficinaPolicy
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
        return $user->can('Listar Oficinas')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver el listado de oficinas.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Oficina  $Oficina
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Oficina $Oficina)
    {
        return $user->can('Ver Oficina')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver esta oficina.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Oficina')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear nuevas oficinas.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Oficina  $Oficina
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Oficina $Oficina)
    {
        return $user->can('Actualizar Oficina')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar esta oficina.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Oficina  $Oficina
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Oficina $Oficina)
    {
        return $user->can('Eliminar Oficina')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar esta oficina.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Oficina  $Oficina
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Oficina $Oficina)
    {
        // Usa el mismo permiso que para eliminar o crea uno específico si es necesario
        return $user->can('Eliminar Oficina');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Oficina  $Oficina
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Oficina $Oficina)
    {
        // Permiso más restrictivo para eliminación permanente
        return $user->hasRole('Administrador') && $user->can('Eliminar Oficina');
    }
}
<?php

namespace App\Policies;

use App\Models\Abonos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AbonoPolicy
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
        return $user->can('Listar Abonos')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar abonos.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonos  $abono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Abonos $abono)
    {
        return $user->can('Ver Abonos')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este abono.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Abonos')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear abonos.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonos  $abono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Abonos $abono)
    {
        return $user->can('Actualizar Abonos')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este abono.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonos  $abono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Abonos $abono)
    {
        return $user->can('Eliminar Abono')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este abono.');
    }
}
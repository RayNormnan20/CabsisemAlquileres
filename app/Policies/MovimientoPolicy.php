<?php

namespace App\Policies;

use App\Models\Movimiento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MovimientoPolicy
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
        return $user->can('Listar Movimientos')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar movimientos.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Movimiento $movimiento)
    {
        return $user->can('Ver Movimiento')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este movimiento.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Movimiento')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear movimientos.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Movimiento $movimiento)
    {
        return $user->can('Actualizar Movimiento')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este movimiento.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Movimiento $movimiento)
    {
        return $user->can('Eliminar Movimiento')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este movimiento.');
    }
}
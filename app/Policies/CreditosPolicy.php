<?php

namespace App\Policies;

use App\Models\Creditos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CreditosPolicy
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
        return $user->can('Listar Creditos')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar créditos.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Creditos  $credito
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Creditos $credito)
    {
        return $user->can('Ver Creditos')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este crédito.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Creditos')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear créditos.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Creditos  $credito
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Creditos $credito)
    {
        return $user->can('Actualizar Creditos')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este crédito.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Creditos  $credito
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Creditos $credito)
    {
        // Solo permitir eliminación si el crédito no tiene abonos
        return $user->can('Eliminar Creditos') && 
               $credito->abonos()->count() === 0
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este crédito o el crédito ya tiene abonos asociados.');
    }
}
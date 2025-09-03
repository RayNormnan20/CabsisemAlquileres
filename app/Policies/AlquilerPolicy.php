<?php

namespace App\Policies;

use App\Models\Alquiler;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AlquilerPolicy
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
        return $user->can('Listar Alquilers')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar alquileres.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Alquiler $alquiler)
    {
        return $user->can('Ver Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este alquiler.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear alquileres.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Alquiler $alquiler)
    {
        return $user->can('Actualizar Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este alquiler.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Alquiler $alquiler)
    {
        return $user->can('Eliminar Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este alquiler.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Alquiler $alquiler)
    {
        return $user->can('Restaurar Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para restaurar este alquiler.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Alquiler $alquiler)
    {
        return $user->can('Eliminar Permanentemente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este alquiler.');
    }

    /**
     * Determine whether the user can finalize rental contracts.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function finalize(User $user, Alquiler $alquiler)
    {
        return $user->can('Finalizar Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para finalizar este alquiler.');
    }

    /**
     * Determine whether the user can suspend rental contracts.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Alquiler  $alquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function suspend(User $user, Alquiler $alquiler)
    {
        return $user->can('Suspender Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para suspender este alquiler.');
    }

    /**
     * Determine whether the user can manage rental contracts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user)
    {
        return $user->can('Gestionar Alquileres')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar alquileres.');
    }
}
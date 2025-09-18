<?php

namespace App\Policies;

use App\Models\PagoAlquiler;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PagoAlquilerPolicy
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
        return $user->can('Listar Pagos Alquilers')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar pagos de alquiler.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PagoAlquiler  $pagoAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, PagoAlquiler $pagoAlquiler)
    {
        return $user->can('Ver Pagos Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este pago de alquiler.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Pagos Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para registrar pagos de alquiler.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PagoAlquiler  $pagoAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PagoAlquiler $pagoAlquiler)
    {
        return $user->can('Actualizar Pagos Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este pago de alquiler.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PagoAlquiler  $pagoAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PagoAlquiler $pagoAlquiler)
    {
        return $user->can('Eliminar Pagos Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este pago de alquiler.');
    }



    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PagoAlquiler  $pagoAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, PagoAlquiler $pagoAlquiler)
    {
        return $user->can('Eliminar Permanentemente Pago Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este pago de alquiler.');
    }


}
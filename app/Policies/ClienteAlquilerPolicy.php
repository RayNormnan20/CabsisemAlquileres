<?php

namespace App\Policies;

use App\Models\ClienteAlquiler;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ClienteAlquilerPolicy
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
        return $user->can('Listar Cliente Alquilers')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar clientes de alquiler.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Ver Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este cliente de alquiler.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear clientes de alquiler.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Actualizar Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este cliente de alquiler.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Eliminar Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este cliente de alquiler.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Restaurar Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para restaurar este cliente de alquiler.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Eliminar Permanentemente Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este cliente de alquiler.');
    }

    /**
     * Determine whether the user can activate/deactivate clients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleStatus(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Cambiar Estado Cliente Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para cambiar el estado de este cliente.');
    }

    /**
     * Determine whether the user can assign routes to clients.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignRoute(User $user)
    {
        return $user->can('Asignar Ruta Cliente')
            ? Response::allow()
            : Response::deny('No tienes permiso para asignar rutas a clientes.');
    }

    /**
     * Determine whether the user can manage rental clients.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user)
    {
        return $user->can('Gestionar Clientes Alquiler')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar clientes de alquiler.');
    }

    /**
     * Determine whether the user can view client rental history.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClienteAlquiler  $clienteAlquiler
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewRentalHistory(User $user, ClienteAlquiler $clienteAlquiler)
    {
        return $user->can('Ver Historial Alquileres Cliente')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver el historial de alquileres de este cliente.');
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YapeCliente;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class YapeClientePolicy
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
        return $user->can('Listar YapeClientes');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\YapeCliente  $yapeCliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, YapeCliente $yapeCliente)
    {
        return $user->can('Ver YapeCliente');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear YapeCliente');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\YapeCliente  $yapeCliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, YapeCliente $yapeCliente)
    {
        return $user->can('Actualizar YapeCliente');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\YapeCliente  $yapeCliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, YapeCliente $yapeCliente)
    {
        // Solo permitir eliminación si el usuario tiene permisos y el YapeCliente no tiene abonos
        return $user->can('Eliminar YapeCliente') && 
               $yapeCliente->abonos()->count() === 0
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este Yape Cliente o el Yape Cliente ya tiene pagos asociados.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\YapeCliente  $yapeCliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, YapeCliente $yapeCliente)
    {
        return $user->can('Actualizar YapeCliente');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\YapeCliente  $yapeCliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, YapeCliente $yapeCliente)
    {
        return $user->hasRole('Administrador') && $user->can('Eliminar YapeCliente');
    }
}
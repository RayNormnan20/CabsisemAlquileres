<?php

namespace App\Policies;

use App\Models\EstadoDepartamento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EstadoDepartamentoPolicy
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
        return $user->can('Listar Estados Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar estados de departamento.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Ver Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este estado de departamento.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear estados de departamento.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Actualizar Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este estado de departamento.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Eliminar Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este estado de departamento.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Restaurar Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para restaurar este estado de departamento.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Eliminar Permanentemente Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este estado de departamento.');
    }

    /**
     * Determine whether the user can toggle the active status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EstadoDepartamento  $estadoDepartamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleActive(User $user, EstadoDepartamento $estadoDepartamento)
    {
        return $user->can('Cambiar Estado Activo Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para cambiar el estado activo de este estado de departamento.');
    }

    /**
     * Determine whether the user can manage all department states.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user)
    {
        return $user->can('Gestionar Estados Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar estados de departamento.');
    }

    /**
     * Determine whether the user can view department state usage statistics.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewUsageStats(User $user)
    {
        return $user->can('Ver Estadísticas Estados Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver estadísticas de estados de departamento.');
    }
}
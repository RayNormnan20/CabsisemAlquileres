<?php

namespace App\Policies;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class DepartamentoPolicy
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
        return $user->can('Listar Departamentos')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar departamentos.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Departamento $departamento)
    {
        return $user->can('Ver Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este departamento.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear departamentos.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Departamento $departamento)
    {
        return $user->can('Actualizar Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este departamento.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Departamento $departamento)
    {
        return $user->can('Eliminar Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este departamento.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Departamento $departamento)
    {
        return $user->can('Restaurar Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para restaurar este departamento.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Departamento $departamento)
    {
        return $user->can('Eliminar Permanentemente Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar permanentemente este departamento.');
    }

    /**
     * Determine whether the user can change department status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function changeStatus(User $user, Departamento $departamento)
    {
        return $user->can('Cambiar Estado Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para cambiar el estado de este departamento.');
    }

    /**
     * Determine whether the user can assign departments to routes.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignRoute(User $user)
    {
        return $user->can('Asignar Ruta Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para asignar rutas a departamentos.');
    }

    /**
     * Determine whether the user can manage departments.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user)
    {
        return $user->can('Gestionar Departamentos')
            ? Response::allow()
            : Response::deny('No tienes permiso para gestionar departamentos.');
    }

    /**
     * Determine whether the user can view department availability.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAvailability(User $user)
    {
        return $user->can('Ver Disponibilidad Departamentos')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver la disponibilidad de departamentos.');
    }

    /**
     * Determine whether the user can view department rental history.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewRentalHistory(User $user, Departamento $departamento)
    {
        return $user->can('Ver Historial Alquileres Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver el historial de alquileres de este departamento.');
    }

    /**
     * Determine whether the user can activate/deactivate departments.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function toggleActive(User $user, Departamento $departamento)
    {
        return $user->can('Activar Desactivar Departamento')
            ? Response::allow()
            : Response::deny('No tienes permiso para activar/desactivar este departamento.');
    }
}
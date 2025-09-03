<?php

namespace App\Policies;

use App\Models\DiaNoLaborable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class DiaNoLaborablePolicy
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
        return $user->can('Listar Dia No Laborables')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar días no laborables.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiaNoLaborable  $diaNoLaborable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, DiaNoLaborable $diaNoLaborable)
    {
        return $user->can('Ver Dia No Laborable')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este día no laborable.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Dia No Laborable')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear días no laborables.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiaNoLaborable  $diaNoLaborable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, DiaNoLaborable $diaNoLaborable)
    {
        return $user->can('Actualizar Dia No Laborable')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este día no laborable.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiaNoLaborable  $diaNoLaborable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, DiaNoLaborable $diaNoLaborable)
    {
        return $user->can('Eliminar Dia No Laborable')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este día no laborable.');
    }
}
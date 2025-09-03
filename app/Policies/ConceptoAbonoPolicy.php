<?php

namespace App\Policies;

use App\Models\ConceptoAbono;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ConceptoAbonoPolicy
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
        return $user->can('Listar Concepto Abonos')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar conceptos de abonos.');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConceptoAbono  $conceptoAbono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ConceptoAbono $conceptoAbono)
    {
        return $user->can('Ver Concepto Abono')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver este concepto de abono.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Crear Concepto Abono')
            ? Response::allow()
            : Response::deny('No tienes permiso para crear conceptos de abonos.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConceptoAbono  $conceptoAbono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ConceptoAbono $conceptoAbono)
    {
        return $user->can('Actualizar Concepto Abono')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar este concepto de abono.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConceptoAbono  $conceptoAbono
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ConceptoAbono $conceptoAbono)
    {
        return $user->can('Eliminar Concepto Abono')
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este concepto de abono.');
    }
}
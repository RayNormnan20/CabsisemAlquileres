<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TrasladarClientePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the trasladar clientes functionality.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('Trasladar Clientes')
            ? Response::allow()
            : Response::deny('No tienes permiso para acceder a la funcionalidad de trasladar clientes.');
    }
}
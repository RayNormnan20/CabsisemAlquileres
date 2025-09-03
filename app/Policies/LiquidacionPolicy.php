<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LiquidacionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the liquidaciones functionality.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('Listar Liquidaciones')
            ? Response::allow()
            : Response::deny('No tienes permiso para acceder a las liquidaciones.');
    }


}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ruta;

class CheckRutaAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Obtener la ruta desde el parámetro (puede ser ID o modelo)
        $rutaParam = $request->route('ruta');
        $ruta = is_object($rutaParam) ? $rutaParam : Ruta::findOrFail($rutaParam);

        // Super Admin tiene acceso total
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Administrador y Encargado de oficina: acceso si es de su oficina
        if ($user->hasAnyRole(['Administrador', 'Encargado de oficina'])) {
            if ($ruta->id_oficina == $user->id_oficina) {
                return $next($request);
            }
            abort(403, 'No tienes acceso a rutas de otras oficinas');
        }

        // Cobrador: solo rutas asignadas
        if ($user->hasRole('Cobrador') && $user->rutas()->where('ruta.id_ruta', $ruta->id_ruta)->exists()) {
            return $next($request);
        }

        // Revisador: lógica personalizada
        if ($user->hasRole('Revisador') && $this->tieneAccesoRevisador($user, $ruta)) {
            return $next($request);
        }

        // Por defecto: denegar acceso
        abort(403, 'Acceso no autorizado');
    }

    protected function tieneAccesoRevisador($user, Ruta $ruta): bool
    {
        return $user->rutasRevisables()->where('ruta.id_ruta', $ruta->id_ruta)->exists();
    }
}

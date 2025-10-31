<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ruta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureSelectedRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    
    /* public function handle(Request $request, Closure $next): Response
    { 
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        if (!Session::has('selected_ruta_id') || Session::get('selected_ruta_id') === null) {
            if ($user->hasRole('Administrador')) {
                $ruta = \App\Models\Ruta::where('activa', true)->first();
            } else {
                $ruta = $user->rutas()->where('activa', true)->first();
            }

            if ($ruta) {
                Session::put('selected_ruta_id', $ruta->id_ruta);
                Session::put('selected_ruta_name', $ruta->nombre_completo ?? $ruta->nombre);
            } else {
                Session::put('selected_ruta_id', null);
                Session::put('selected_ruta_name', 'Ruta');
            }
        }

        return $next($request);
    } */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Forzar sincronización con la ruta persistida en el primer request autenticado
        $initializedKey = 'route_initialized_for_user';
        $alreadyInitialized = Session::get($initializedKey) === $user->id;
        $currentSelected = Session::get('selected_ruta_id');
        $persistedSelected = !empty($user->last_selected_ruta_id) ? (int) $user->last_selected_ruta_id : null;

        // Si hay una ruta en sesión que el usuario no puede acceder, forzar reinicialización
        $currentSelectedInvalid = false;
        if ($currentSelected) {
            if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                $currentSelectedInvalid = false;
            } else {
                $currentSelectedInvalid = !$user->rutas()->where('ruta.id_ruta', $currentSelected)->exists();
            }
        }

        // Inicializar si nunca se hizo o si la sesión no coincide con lo persistido
        if (!$alreadyInitialized || $currentSelected !== $persistedSelected || $currentSelectedInvalid) {
            $ruta = null;

            // 1) Priorizar la ruta persistida en el usuario
            if (!empty($user->last_selected_ruta_id)) {
                $ruta = Ruta::where('id_ruta', $user->last_selected_ruta_id)->first();
            }

            // 2) Si no existe, caer a la primera ruta activa del usuario
            if (!$ruta) {
                $ruta = $user->rutas()->where('activa', true)->first();
            }

            // 3) Si el usuario no tiene rutas activas asignadas, tomar la primera activa global
            if (!$ruta) {
                $ruta = Ruta::where('activa', true)->first();
            }

            $rutaId = $ruta ? $ruta->id_ruta : null;
            $rutaName = $ruta ? ($ruta->nombre_completo ?? $ruta->nombre) : 'Ruta';

            Session::put('selected_ruta_id', $rutaId);
            Session::put('selected_ruta_name', $rutaName);

            session()->flash('set_initial_route_event', [
                'id' => $rutaId,
                'name' => $rutaName,
            ]);

            // Marcar que ya inicializamos la ruta para este usuario en esta sesión
            Session::put($initializedKey, $user->id);
        }

        return $next($request);
    }
}

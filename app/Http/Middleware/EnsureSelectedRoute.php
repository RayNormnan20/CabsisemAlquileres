<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        if (!Session::has('selected_ruta_id') || Session::get('selected_ruta_id') === null) {
            $ruta = $user->rutas()->where('activa', true)->first();

            if ($ruta) {
                $rutaId = $ruta->id_ruta;
                $rutaName = $ruta->nombre_completo ?? $ruta->nombre;
            } else {
                $rutaId = null;
                $rutaName = 'Ruta';
            }

            Session::put('selected_ruta_id', $rutaId);
            Session::put('selected_ruta_name', $rutaName);

            session()->flash('set_initial_route_event', [
                'id' => $rutaId,
                'name' => $rutaName,
            ]);
        }

        return $next($request);
    }
}

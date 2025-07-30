<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
    
    public function handle(Request $request, Closure $next): Response
    { 
        $user = auth()->user();
        if (!Session::has('selected_ruta_id') || Session::get('selected_ruta_id') === null) {
            if ($user) {
                if ($user->hasRole('Administrador')) {
                    Session::put('selected_ruta_id', null);
                    Session::put('selected_ruta_name', 'Ruta');
                } else {
                    $ruta = $user->ruta_principal; 
                    if ($ruta) {
                        Session::put('selected_ruta_id', $ruta->id_ruta);
                        Session::put('selected_ruta_name', $ruta->nombre_completo ?? $ruta->nombre);
                    } else {
                        Session::put('selected_ruta_id', null);
                        Session::put('selected_ruta_name', 'Ruta');
                    }
                }
            } else {
                Session::put('selected_ruta_id', null);
                Session::put('selected_ruta_name', 'Ruta');
            }
        }

        if (!Session::has('selected_ruta_name') || Session::get('selected_ruta_name') === null) {
            Session::put('selected_ruta_name', 'Ruta');
        }

        return $next($request);
    }
}

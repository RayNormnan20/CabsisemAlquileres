<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClearDailyLoginSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si hay un celular almacenado en sesión
        if ($request->session()->has('daily_login_phone')) {
            $lastLoginDate = $request->session()->get('daily_login_date');
            $today = Carbon::today()->toDateString();
            
            // Si no hay fecha almacenada o es diferente a hoy, limpiar la sesión
            if (!$lastLoginDate || $lastLoginDate !== $today) {
                $request->session()->forget(['daily_login_phone', 'daily_login_date']);
            }
        }
        
        return $next($request);
    }
}

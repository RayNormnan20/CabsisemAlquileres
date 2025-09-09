<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class MobileSessionManager
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
        // Excluir rutas de Livewire y Filament para evitar conflictos
        if ($request->is('livewire/*') || $request->is('filament/*') || $request->is('*/livewire/*')) {
            return $next($request);
        }
        
        $agent = new Agent();
        
        // Detectar si es un dispositivo móvil
        if ($agent->isMobile() || $agent->isTablet()) {
            // Marcar la sesión como móvil
            session(['is_mobile_device' => true]);
            
            // Si es una petición AJAX para logout móvil
            if ($request->is('mobile-logout') && $request->isMethod('post')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sesión cerrada exitosamente'
                ]);
            }
        } else {
            session(['is_mobile_device' => false]);
        }
        
        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            
            Log::info('Mobile session detected', [
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'route' => $request->path()
            ]);
            
            // Si es una petición AJAX para logout móvil
            if ($request->is('mobile-logout') && $request->isMethod('post')) {
                // Preservar datos de login diario antes de invalidar la sesión
                $dailyLoginPhone = $request->session()->get('daily_login_phone');
                $dailyLoginDate = $request->session()->get('daily_login_date');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Restaurar datos de login diario para permitir reingreso solo con contraseña
                if ($dailyLoginPhone && $dailyLoginDate) {
                    $request->session()->put('daily_login_phone', $dailyLoginPhone);
                    $request->session()->put('daily_login_date', $dailyLoginDate);
                }

                Log::info('Mobile logout executed (preserving daily login)', [
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                    'timestamp' => now(),
                    'preserved_phone' => $dailyLoginPhone ?? null,
                    'preserved_date' => $dailyLoginDate ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sesión cerrada exitosamente desde dispositivo móvil'
                ]);
            }
        } else {
            session(['is_mobile_device' => false]);
        }
        
        return $next($request);
    }
}
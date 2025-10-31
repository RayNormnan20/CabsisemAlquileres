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
                // Preservar la ruta seleccionada antes de invalidar la sesión
                $selectedRutaId = $request->session()->get('selected_ruta_id');
                $selectedRutaName = $request->session()->get('selected_ruta_name');

                // Preservar el cliente seleccionado en Créditos
                $selectedCreditosClienteId = $request->session()->get('creditos_cliente_id');
                // Preservar el cliente seleccionado en Abonos
                $selectedAbonosClienteId = $request->session()->get('abonos_cliente_id');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Restaurar datos de login diario para permitir reingreso solo con contraseña
                if ($dailyLoginPhone && $dailyLoginDate) {
                    $request->session()->put('daily_login_phone', $dailyLoginPhone);
                    $request->session()->put('daily_login_date', $dailyLoginDate);
                }

                // No restaurar la ruta seleccionada tras reingreso.
                // La ruta inicial debe sincronizarse según el usuario autenticado
                // en el middleware EnsureSelectedRoute para evitar filtraciones entre usuarios.

                // Restaurar el cliente seleccionado en Créditos tras reingreso
                if (!is_null($selectedCreditosClienteId)) {
                    $request->session()->put('creditos_cliente_id', $selectedCreditosClienteId);
                }
                // Restaurar el cliente seleccionado en Abonos tras reingreso
                if (!is_null($selectedAbonosClienteId)) {
                    $request->session()->put('abonos_cliente_id', $selectedAbonosClienteId);
                }

                Log::info('Mobile logout executed (preserving daily login)', [
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                    'timestamp' => now(),
                    'preserved_phone' => $dailyLoginPhone ?? null,
                    'preserved_date' => $dailyLoginDate ?? null,
                    'preserved_ruta_id' => null,
                    'preserved_ruta_name' => null,
                    'preserved_creditos_cliente_id' => $selectedCreditosClienteId ?? null,
                    'preserved_abonos_cliente_id' => $selectedAbonosClienteId ?? null,
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
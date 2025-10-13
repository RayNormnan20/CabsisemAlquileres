<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\YapeClienteController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\CalcLoginController;

// Validate an account
Route::get('/validate-account/{user:creation_token}', function (User $user) {
    return view('validate-account', compact('user'));
})
    ->name('validate-account')
    ->middleware([
        'web',
        DispatchServingFilamentEvent::class
    ]);


    // Login default redirection
//Route::redirect('/login-redirect', '/login')->name('login');


// Login calculadora (GET muestra la vista, POST autentica)
Route::get('/login', function () {
    return view('auth.calc-login');
})->middleware(['web'])->name('login');

Route::post('/login', [CalcLoginController::class, 'authenticate'])
    ->middleware(['web'])
    ->name('login.post');

// Mobile logout route
Route::post('/mobile-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'message' => 'Sesión cerrada exitosamente desde dispositivo móvil'
    ]);
})->middleware(['web'])->name('mobile.logout');

// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });


Route::middleware(['auth', 'check.ruta.access'])->group(function () {
    Route::get('/rutas/{ruta}', [RutaController::class, 'show'])->name('rutas.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});

Route::post('/creditos/actualizar', [CreditoController::class, 'update'])->name('creditos.actualizar');
Route::post('/creditos/renovar', [CreditoController::class, 'renovar'])->name('creditos.renovar');
Route::post('/creditos/cancelar', [CreditoController::class, 'cancelar'])->name('creditos.cancelar');
Route::post('/planilla-recaudador/renovacion', [\App\Filament\Resources\PlanillaRecaudadorResource\Pages\ListPlanillaRecaudadors::class, 'handleRenovacionAction'])->middleware(['auth'])->name('planilla-recaudador.renovacion');
Route::get('/creditos/{credito}/yape-cliente', [CreditoController::class, 'getYapeCliente'])->name('creditos.yape-cliente');
Route::get('/clientes/{cliente}/yape-cliente-completo', [CreditoController::class, 'getYapeClienteCompleto'])->name('clientes.yape-cliente-completo');
Route::get('/clientes/{cliente}/yape-clientes', [CreditoController::class, 'getYapeClientes'])->name('clientes.yape-clientes');

// Guardar horarios de acceso del usuario autenticado
Route::post('/usuarios/horario', [\App\Http\Controllers\UserAccessController::class, 'saveHours'])->middleware(['auth']);

// NUEVAS RUTAS CON CACHÉ (SOLO AGREGAR)
Route::get('/creditos/activos', [CreditoController::class, 'index'])->name('creditos.activos');
Route::get('/creditos/vencidos', [CreditoController::class, 'vencidos'])->name('creditos.vencidos');
Route::get('/creditos/estadisticas', [CreditoController::class, 'estadisticas'])->name('creditos.estadisticas');
Route::get('/creditos/conceptos', [CreditoController::class, 'getConceptosConCache'])->name('creditos.conceptos');
Route::post('/creditos/limpiar-cache', [CreditoController::class, 'limpiarCache'])->name('creditos.limpiar-cache');

Route::get('/clientes/{id}', [YapeClienteController::class, 'getClienteInfo']);
Route::get('/cobradores-por-ruta/{rutaId}', [YapeClienteController::class, 'getCobradoresPorRuta']);

// Nueva ruta para generar PDF de pagos de Yape Cliente
Route::get('/yape-cliente/{id}/pdf', [YapeClienteController::class, 'generarPDF'])->name('yape-cliente.pdf');

// Ruta API para obtener datos de resumen de alquiler
Route::get('/admin/pagos-alquiler/get-resumen-data/{alquilerId}', function ($alquilerId) {
    try {
        // Importar las clases necesarias
        $alquiler = \App\Models\Alquiler::with([
            'pagos' => function($query) {
                $query->with('usuarioRegistro')->orderBy('fecha_pago', 'desc');
            },
            'inquilino'
        ])->find($alquilerId);
        
        if (!$alquiler) {
            return response()->json(['error' => 'Alquiler no encontrado'], 404);
        }
        
        $pagosRealizados = $alquiler->pagos;
        
        // Generar pagos mensuales como en el componente original
        $fechaInicio = \Carbon\Carbon::parse($alquiler->fecha_inicio);
        $fechaActual = \Carbon\Carbon::now();
        $fechaFin = $alquiler->fecha_fin ? \Carbon\Carbon::parse($alquiler->fecha_fin) : null;
        $pagosMensuales = [];
        
        $fechaLimite = $fechaFin && $fechaFin->lt($fechaActual) ? $fechaFin : $fechaActual;
        $fechaInicio = $fechaInicio->copy()->startOfMonth();
        $fechaMes = $fechaInicio->copy();
        
        while ($fechaMes->lte($fechaLimite->startOfMonth())) {
            $nombreMes = $fechaMes->locale('es')->isoFormat('MMMM YYYY');
            
            // Calcular la suma de abonos para este mes específico
            $abonosDelMes = $pagosRealizados->where('mes_correspondiente', $fechaMes->month)
                ->where('ano_correspondiente', $fechaMes->year)
                ->sum('monto_pagado');
            
            $estado = 'PENDIENTE';
            if ($abonosDelMes > 0) {
                if ($abonosDelMes >= $alquiler->precio_mensual) {
                    $estado = 'CANCELADO';
                } else {
                    $estado = 'PAGO PARCIAL';
                }
            } else {
                if ($fechaMes->lt(\Carbon\Carbon::now()->startOfMonth())) {
                    $estado = 'DEUDA PENDIENTE';
                }
            }
            
            $pagosMensuales[] = [
                'mes' => ucfirst($nombreMes),
                'total' => $alquiler->precio_mensual,
                'pagado' => $abonosDelMes,
                'estado' => $estado,
                'fecha' => $fechaMes->copy()
            ];
            
            $fechaMes->addMonth();
        }
        
        // Calcular detalles de pagos
        $detallesPagos = [];
        foreach ($pagosRealizados as $pago) {
            $detallesPagos[] = [
                'id' => $pago->id_pago_alquiler,
                'fecha_pago' => $pago->fecha_pago,
                'mes_correspondiente' => $pago->mes_correspondiente,
                'ano_correspondiente' => $pago->ano_correspondiente,
                'monto_pagado' => $pago->monto_pagado,
                'metodo_pago' => $pago->metodo_pago,
                'referencia_pago' => $pago->referencia_pago,
                'observaciones' => $pago->observaciones,
                'cliente_nombre' => $alquiler->inquilino->nombre_completo ?? 'N/A',
                'cobrador_nombre' => $pago->usuarioRegistro->name ?? 'N/A'
            ];
        }
        
        return response()->json([
            'success' => true,
            'alquilerId' => $alquilerId,
            'pagosMensuales' => $pagosMensuales,
            'detallesPagos' => $detallesPagos,
            'count' => count($pagosMensuales)
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error obteniendo datos de resumen de alquiler: ' . $e->getMessage());
        return response()->json(['error' => 'Error interno del servidor', 'message' => $e->getMessage()], 500);
    }
})->middleware(['auth'])->name('pagos-alquiler.get-resumen-data');

Route::middleware(['auth'])->group(function () {
    // Rutas comunes

    // Rutas protegidas por política
    Route::middleware(['can:access-ruta,ruta'])->group(function () {
        Route::get('/rutas/{ruta}', [RutaController::class, 'show'])->name('rutas.show');
        Route::get('/rutas/{ruta}/clientes', [RutaController::class, 'clientes'])->name('rutas.clientes');
    });

    // Comentar estas rutas ya que usamos Filament para gestionar rutas
    /*
    // Rutas de gestión
    Route::middleware(['can:manage-rutas'])->group(function () {
        Route::resource('rutas', RutaController::class)->except(['show', 'index']);
    });
    */

    // Rutas de cobros
    Route::middleware(['can:collect-payments', 'can:access-ruta,ruta'])->post('/rutas/{ruta}/abonos', [RutaController::class, 'storePayment']);

    // Rutas de reportes
    Route::middleware(['can:view-ruta-reports'])->group(function () {
        Route::get('/rutas/{ruta}/reportes', [RutaController::class, 'reportes'])->name('rutas.reports');
    });
});

// Ruta para prueba de WebSockets
Route::get('/websocket-test', function () {
    return view('websocket-test');
})->middleware(['auth'])->name('websocket.test');

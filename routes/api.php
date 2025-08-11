<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Endpoint para verificar estado del caché en producción
Route::get('/cache-status', function () {
    try {
        $start = microtime(true);
        
        // Probar consulta con caché
        $creditos = \App\Models\Creditos::getCreditosActivosConCache();
        $tiempo_con_cache = (microtime(true) - $start) * 1000;
        
        $start = microtime(true);
        
        // Probar consulta sin caché (directa) - CORREGIDO: usar saldo_actual en lugar de estado
        $creditos_directo = \App\Models\Creditos::where('saldo_actual', '>', 0)->get();
        $tiempo_sin_cache = (microtime(true) - $start) * 1000;
        
        $mejora = $tiempo_sin_cache > 0 ? $tiempo_sin_cache / $tiempo_con_cache : 1;
        
        return response()->json([
            'status' => 'success',
            'cache_driver' => config('cache.default'),
            'cache_funcionando' => Cache::has('creditos_activos_all'),
            'total_creditos' => $creditos->count(),
            'tiempo_con_cache_ms' => round($tiempo_con_cache, 2),
            'tiempo_sin_cache_ms' => round($tiempo_sin_cache, 2),
            'mejora_rendimiento' => round($mejora, 2) . 'x',
            'cache_keys_activas' => [
                'creditos_activos_all' => Cache::has('creditos_activos_all'),
                'estadisticas_creditos_all' => Cache::has('estadisticas_creditos_all'),
                'creditos_vencidos_all' => Cache::has('creditos_vencidos_all')
            ],
            'servidor_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'cache_path_exists' => file_exists(storage_path('framework/cache/data')),
                'storage_writable' => is_writable(storage_path()),
                'archivos_cache' => count(glob(storage_path('framework/cache/data/*/*')))
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'cache_driver' => config('cache.default'),
            'servidor_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'cache_path_exists' => file_exists(storage_path('framework/cache/data')),
                'storage_writable' => is_writable(storage_path())
            ]
        ], 500);
    }
});

// Endpoint para limpiar caché manualmente
Route::post('/cache-clear', function () {
    try {
        Cache::flush();
        return response()->json([
            'status' => 'success',
            'message' => 'Caché limpiado correctamente'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// ✅ NUEVAS RUTAS API CON CACHÉ (agregadas al final)

// API para obtener créditos activos con caché
Route::get('/creditos/activos', function (Request $request) {
    try {
        $rutaId = $request->get('ruta_id');
        $creditos = \App\Models\Creditos::getCreditosActivosConCache($rutaId);
        
        return response()->json([
            'status' => 'success',
            'data' => $creditos,
            'total' => $creditos->count(),
            'cached' => true,
            'ruta_filtro' => $rutaId ? "Ruta ID: $rutaId" : 'Todas las rutas'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// API para obtener créditos vencidos con caché
Route::get('/creditos/vencidos', function (Request $request) {
    try {
        $rutaId = $request->get('ruta_id');
        $creditos = \App\Models\Creditos::getCreditosVencidosConCache($rutaId);
        
        return response()->json([
            'status' => 'success',
            'data' => $creditos,
            'total' => $creditos->count(),
            'cached' => true,
            'ruta_filtro' => $rutaId ? "Ruta ID: $rutaId" : 'Todas las rutas'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// API para obtener estadísticas con caché
Route::get('/creditos/estadisticas', function (Request $request) {
    try {
        $rutaId = $request->get('ruta_id');
        $estadisticas = \App\Models\Creditos::getEstadisticasCreditosConCache($rutaId);
        
        return response()->json([
            'status' => 'success',
            'data' => $estadisticas,
            'cached' => true,
            'ruta_filtro' => $rutaId ? "Ruta ID: $rutaId" : 'Todas las rutas'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// API para obtener resumen completo con caché
Route::get('/creditos/resumen', function (Request $request) {
    try {
        $rutaId = $request->get('ruta_id');
        
        $start = microtime(true);
        
        $resumen = [
            'creditos_activos' => \App\Models\Creditos::getCreditosActivosConCache($rutaId),
            'creditos_vencidos' => \App\Models\Creditos::getCreditosVencidosConCache($rutaId),
            'estadisticas' => \App\Models\Creditos::getEstadisticasCreditosConCache($rutaId)
        ];
        
        $tiempo_respuesta = (microtime(true) - $start) * 1000;
        
        return response()->json([
            'status' => 'success',
            'data' => $resumen,
            'cached' => true,
            'tiempo_respuesta_ms' => round($tiempo_respuesta, 2),
            'ruta_filtro' => $rutaId ? "Ruta ID: $rutaId" : 'Todas las rutas',
            'totales' => [
                'activos' => $resumen['creditos_activos']->count(),
                'vencidos' => $resumen['creditos_vencidos']->count()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// API para limpiar caché específico de créditos
Route::post('/creditos/limpiar-cache', function () {
    try {
        \App\Models\Creditos::limpiarCacheCredito();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Caché de créditos limpiado correctamente',
            'cache_keys_limpiadas' => [
                'creditos_activos_all',
                'creditos_vencidos_all', 
                'estadisticas_creditos_all',
                'conceptos_creditos_all'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

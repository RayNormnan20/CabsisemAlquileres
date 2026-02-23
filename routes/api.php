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

// Endpoints de créditos eliminados

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCreditosCache extends Command
{
    protected $signature = 'cache:clear-creditos {--ruta= : ID de la ruta específica}';
    protected $description = 'Limpiar caché específico de créditos';

    public function handle()
    {
        $rutaId = $this->option('ruta');
        
        if ($rutaId) {
            // Limpiar caché de una ruta específica
            Cache::forget("creditos_activos_ruta_{$rutaId}");
            Cache::forget("creditos_vencidos_ruta_{$rutaId}");
            Cache::forget("estadisticas_creditos_ruta_{$rutaId}");
            
            $this->info("Caché de créditos limpiado para la ruta {$rutaId}");
        } else {
            // Limpiar todo el caché de créditos
            $keys = [
                'creditos_activos_all',
                'creditos_vencidos_all',
                'estadisticas_creditos_all',
                'conceptos_creditos'
            ];
            
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            
            $this->info('Todo el caché de créditos ha sido limpiado');
        }
        
        return 0;
    }
}
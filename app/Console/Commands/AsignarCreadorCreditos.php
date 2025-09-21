<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Creditos;
use App\Models\LogActividad;
use Illuminate\Support\Facades\DB;

class AsignarCreadorCreditos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creditos:asignar-creador {--dry-run : Ejecutar en modo de prueba sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna el usuario creador a los créditos basándose en los logs de actividad';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Ejecutando en modo de prueba (dry-run) - No se realizarán cambios');
        }

        $this->info('🚀 Iniciando proceso de asignación de creadores de créditos...');

        // Obtener todos los créditos sin id_usuario_creador
        $creditosSinCreador = Creditos::whereNull('id_usuario_creador')->get();
        
        $this->info("📊 Encontrados {$creditosSinCreador->count()} créditos sin usuario creador asignado");

        if ($creditosSinCreador->isEmpty()) {
            $this->info('✅ No hay créditos sin usuario creador. El proceso ha terminado.');
            return 0;
        }

        $creditosActualizados = 0;
        $creditosNoEncontrados = 0;

        $progressBar = $this->output->createProgressBar($creditosSinCreador->count());
        $progressBar->start();

        foreach ($creditosSinCreador as $credito) {
            // Buscar en los logs de actividad
            $logActividad = $this->buscarLogCreacion($credito);
            
            if ($logActividad) {
                if (!$dryRun) {
                    // Actualizar el crédito con el usuario creador
                    $credito->update(['id_usuario_creador' => $logActividad->user_id]);
                }
                
                $creditosActualizados++;
                
                if ($dryRun) {
                    $this->newLine();
                    $this->line("🔍 Crédito #{$credito->id_credito} - Cliente: {$credito->cliente->nombre_completo} - Sería asignado a usuario ID: {$logActividad->user_id} ({$logActividad->usuario->name})");
                }
            } else {
                $creditosNoEncontrados++;
                
                if ($dryRun) {
                    $this->newLine();
                    $this->warn("⚠️  Crédito #{$credito->id_credito} - Cliente: {$credito->cliente->nombre_completo} - No se encontró log de creación");
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('📈 RESUMEN DEL PROCESO:');
        $this->table(
            ['Descripción', 'Cantidad'],
            [
                ['Créditos procesados', $creditosSinCreador->count()],
                ['Créditos con creador identificado', $creditosActualizados],
                ['Créditos sin log de creación', $creditosNoEncontrados],
                ['Créditos actualizados', $dryRun ? '0 (modo prueba)' : $creditosActualizados],
            ]
        );

        if ($dryRun) {
            $this->info('💡 Para aplicar los cambios, ejecuta el comando sin la opción --dry-run');
        } else {
            $this->info("✅ Proceso completado. Se actualizaron {$creditosActualizados} créditos.");
        }

        return 0;
    }

    /**
     * Busca el log de actividad que corresponde a la creación del crédito
     */
    private function buscarLogCreacion(Creditos $credito)
    {
        // Buscar logs que mencionen la creación de este crédito específico
        // Primero intentamos buscar por ID del crédito en metadata
        $logPorMetadata = LogActividad::where('tipo', 'Créditos')
            ->where('mensaje', 'like', '%nuevo crédito%')
            ->whereRaw("JSON_EXTRACT(metadata, '$.credito_id') = ?", [$credito->id_credito])
            ->first();

        if ($logPorMetadata) {
            return $logPorMetadata;
        }

        // Si no encontramos por metadata, buscamos por cliente y fecha aproximada
        $fechaCredito = $credito->created_at ?? $credito->fecha_credito;
        $fechaInicio = $fechaCredito->copy()->subHours(2);
        $fechaFin = $fechaCredito->copy()->addHours(2);

        $logPorClienteYFecha = LogActividad::where('tipo', 'Créditos')
            ->where('mensaje', 'like', '%nuevo crédito%')
            ->where('mensaje', 'like', '%' . $credito->cliente->nombre_completo . '%')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->first();

        if ($logPorClienteYFecha) {
            return $logPorClienteYFecha;
        }

        // Último intento: buscar por cliente y valor del crédito
        $logPorClienteYValor = LogActividad::where('tipo', 'Créditos')
            ->where('mensaje', 'like', '%nuevo crédito%')
            ->where('mensaje', 'like', '%' . $credito->cliente->nombre_completo . '%')
            ->whereRaw("JSON_EXTRACT(metadata, '$.valor_credito') = ?", [number_format($credito->valor_credito, 2, '.', '')])
            ->first();

        return $logPorClienteYValor;
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Abonos;
use App\Models\Clientes;
use App\Models\Creditos;
use Illuminate\Console\Command;

class CreateTestAbono extends Command
{
    protected $signature = 'test:create-abono';
    protected $description = 'Crear un abono de prueba para verificar WebSocket';

    public function handle()
    {
        try {
            // Obtener un cliente
            $cliente = \App\Models\Clientes::first();
            if (!$cliente) {
                $this->error('No se encontraron clientes');
                return 1;
            }

            // Obtener un crédito activo del cliente
            $credito = \App\Models\Creditos::where('id_cliente', $cliente->id_cliente)
                ->where('saldo_actual', '>', 0)
                ->first();

            if (!$credito) {
                $this->error('No se encontraron créditos activos para el cliente');
                return 1;
            }

            $this->info("Cliente: {$cliente->nombre} (ID: {$cliente->id_cliente})");
            $this->info("Crédito: {$credito->id_credito}");
            $this->info("Ruta: {$credito->id_ruta}");

            // Datos del abono
            $datosAbono = [
                'id_cliente' => $cliente->id_cliente,
                'id_credito' => $credito->id_credito,
                'id_ruta' => $credito->id_ruta,
                'monto_abono' => 50.00,
                'observaciones' => 'Prueba WebSocket - ' . now(),
                'id_usuario' => 1, // Usuario por defecto
                'fecha_pago' => now(),
                'saldo_anterior' => $credito->saldo_actual,
                'saldo_posterior' => $credito->saldo_actual - 50.00,
            ];

            // Conceptos del abono
            $conceptos = [
                [
                    'id_credito' => $credito->id_credito,
                    'tipo_concepto' => 'Efectivo',
                    'monto' => 50.00,
                ]
            ];

            // Crear el abono usando el método registrarConConceptos
            $abono = \App\Models\Abonos::registrarConConceptos($datosAbono, $conceptos);

            $this->info("✅ Abono creado exitosamente con ID: {$abono->id_abono}");
            $this->info("Monto: S/ {$abono->monto_abono}");
            $this->info("Fecha: {$abono->created_at}");
            $this->info("Conceptos creados: " . $abono->conceptosabonos->count());
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
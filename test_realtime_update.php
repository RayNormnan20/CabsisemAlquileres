<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Abonos;
use App\Models\Creditos;
use App\Models\Clientes;
use App\Models\Concepto;
use App\Events\AbonoCreated;

echo "=== PRUEBA DE ACTUALIZACIÓN EN TIEMPO REAL ===\n";

// Buscar un crédito activo para crear un abono de prueba
$credito = Creditos::where('saldo_actual', '>', 0)->first();

if (!$credito) {
    echo "❌ No se encontraron créditos activos para la prueba\n";
    exit(1);
}

echo "✅ Crédito encontrado: ID {$credito->id_credito}, Cliente: {$credito->cliente->nombre}\n";
echo "   Saldo actual: S/ " . number_format($credito->saldo_actual, 2) . "\n";
echo "   Ruta: {$credito->cliente->ruta->nombre} (ID: {$credito->cliente->id_ruta})\n";

// Obtener concepto "Abono"
$concepto = Concepto::where('nombre', 'Abono')->first();
if (!$concepto) {
    echo "❌ No se encontró el concepto 'Abono'\n";
    exit(1);
}

// Crear un abono de prueba
$montoAbono = 50.00;
$saldoAnterior = $credito->saldo_actual;
$saldoPosterior = $saldoAnterior - $montoAbono;

echo "\n=== CREANDO ABONO DE PRUEBA ===\n";
echo "Monto del abono: S/ " . number_format($montoAbono, 2) . "\n";
echo "Saldo anterior: S/ " . number_format($saldoAnterior, 2) . "\n";
echo "Saldo posterior: S/ " . number_format($saldoPosterior, 2) . "\n";

$abono = new Abonos();
$abono->id_credito = $credito->id_credito;
$abono->id_cliente = $credito->id_cliente;
$abono->id_ruta = $credito->cliente->id_ruta;
$abono->id_usuario = 1; // Usuario por defecto
$abono->id_concepto = $concepto->id;
$abono->fecha_pago = now();
$abono->monto_abono = $montoAbono;
$abono->saldo_anterior = $saldoAnterior;
$abono->saldo_posterior = $saldoPosterior;
$abono->estado = true;
$abono->es_devolucion = false;

try {
    $abono->save();
    echo "✅ Abono creado exitosamente con ID: {$abono->id_abono}\n";
    echo "✅ El modelo Abonos se encarga automáticamente de actualizar el crédito\n";
    
    echo "\n=== EVENTO WEBSOCKET ===\n";
    echo "Canal del evento: ruta.{$abono->id_ruta}\n";
    echo "Evento: abono.created\n";
    echo "Datos del evento:\n";
    echo "- ID Abono: {$abono->id_abono}\n";
    echo "- Cliente: {$abono->cliente->nombre} {$abono->cliente->apellido}\n";
    echo "- Monto: S/ " . number_format($abono->monto_abono, 2) . "\n";
    echo "- Ruta: {$abono->ruta->nombre}\n";
    
    echo "\n✅ PRUEBA COMPLETADA\n";
    echo "Ahora verifica en el navegador si las pestañas se actualizan automáticamente.\n";
    echo "Deberías ver el nuevo abono en ambas pestañas sin necesidad de recargar manualmente.\n";
    
} catch (Exception $e) {
    echo "❌ Error al crear el abono: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== FIN DE LA PRUEBA ===\n";
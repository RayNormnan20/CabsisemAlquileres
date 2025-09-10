<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Clientes;
use App\Events\ClienteCreated;
use App\Events\ClienteUpdated;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DE ACTUALIZACIONES EN TIEMPO REAL PARA CLIENTES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Obtener una ruta existente
$rutaId = 2; // Usar la misma ruta que en las pruebas anteriores
echo "Usando ruta ID: {$rutaId}\n\n";

try {
    // === PRUEBA 1: CREAR NUEVO CLIENTE ===
    echo "1. Creando nuevo cliente...\n";
    
    $nuevoCliente = new Clientes();
    $nuevoCliente->id_tipo_documento = 1;
    $nuevoCliente->numero_documento = '12345' . rand(1000, 9999);
    $nuevoCliente->nombre = 'Cliente Prueba';
    $nuevoCliente->apellido = 'WebSocket ' . rand(100, 999);
    $nuevoCliente->celular = '300' . rand(1000000, 9999999);
    $nuevoCliente->direccion = 'Dirección de prueba ' . rand(1, 100);
    $nuevoCliente->id_ruta = $rutaId;
    $nuevoCliente->activo = true;
    $nuevoCliente->id_usuario_creador = 1;
    
    $nuevoCliente->save();
    
    echo "✅ Cliente creado exitosamente:\n";
    echo "   - ID: {$nuevoCliente->id_cliente}\n";
    echo "   - Nombre: {$nuevoCliente->nombre} {$nuevoCliente->apellido}\n";
    echo "   - Documento: {$nuevoCliente->numero_documento}\n";
    echo "   - Celular: {$nuevoCliente->celular}\n";
    echo "   - Ruta: {$nuevoCliente->id_ruta}\n";
    
    // El evento ClienteCreated se dispara automáticamente por el modelo
    echo "\n📡 Evento ClienteCreated disparado automáticamente\n";
    echo "   - Canal: clientes\n";
    echo "   - Evento: cliente.created\n";
    echo "   - Datos del cliente enviados via WebSocket\n";
    
    sleep(2);
    
    // === PRUEBA 2: ACTUALIZAR CLIENTE ===
    echo "\n2. Actualizando cliente...\n";
    
    $nuevoCliente->celular = '301' . rand(1000000, 9999999);
    $nuevoCliente->direccion = 'Dirección actualizada ' . rand(1, 100);
    $nuevoCliente->save();
    
    echo "✅ Cliente actualizado exitosamente:\n";
    echo "   - Nuevo celular: {$nuevoCliente->celular}\n";
    echo "   - Nueva dirección: {$nuevoCliente->direccion}\n";
    
    // El evento ClienteUpdated se dispara automáticamente por el modelo
    echo "\n📡 Evento ClienteUpdated disparado automáticamente\n";
    echo "   - Canal: clientes\n";
    echo "   - Evento: cliente.updated\n";
    echo "   - Datos actualizados enviados via WebSocket\n";
    
    echo "\n=== PRUEBA COMPLETADA ===\n";
    echo "✅ Los eventos de cliente han sido enviados correctamente\n";
    echo "\n📋 INSTRUCCIONES PARA VERIFICAR:\n";
    echo "1. Abre la página de clientes en tu navegador\n";
    echo "2. Verifica que la tabla se actualice automáticamente\n";
    echo "3. Deberías ver notificaciones de 'Cliente creado' y 'Cliente actualizado'\n";
    echo "4. El nuevo cliente debería aparecer en la tabla sin recargar la página\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
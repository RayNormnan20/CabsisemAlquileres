<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EstadoDepartamento;
use Illuminate\Support\Facades\Log;

echo "=== Prueba de Actualización de Estado Departamento #2 ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Buscar el estado departamento con ID 2
    $estadoDepartamento = EstadoDepartamento::find(2);
    
    if (!$estadoDepartamento) {
        echo "❌ Error: No se encontró el Estado Departamento con ID 2\n";
        exit(1);
    }
    
    echo "📋 Estado Departamento encontrado:\n";
    echo "   ID: {$estadoDepartamento->id_estado_departamento}\n";
    echo "   Nombre: {$estadoDepartamento->nombre}\n";
    echo "   Descripción: {$estadoDepartamento->descripcion}\n";
    echo "   Color: {$estadoDepartamento->color}\n";
    echo "   Activo: " . ($estadoDepartamento->activo ? 'Sí' : 'No') . "\n\n";
    
    // Guardar valores originales
    $nombreOriginal = $estadoDepartamento->nombre;
    $descripcionOriginal = $estadoDepartamento->descripcion;
    
    echo "🔄 Actualizando el registro...\n";
    
    // Actualizar algunos campos
    $estadoDepartamento->update([
        'nombre' => 'Ocupado - Test ' . date('H:i'),
        'descripcion' => 'Departamento en prueba - Modificado en ' . date('H:i:s'),
        'color' => '#FF5722' // Cambiar a color naranja
    ]);
    
    echo "✅ Registro actualizado exitosamente\n";
    echo "   Nuevo nombre: {$estadoDepartamento->nombre}\n";
    echo "   Nueva descripción: {$estadoDepartamento->descripcion}\n";
    echo "   Nuevo color: {$estadoDepartamento->color}\n\n";
    
    echo "📡 Evento EstadoDepartamentoUpdated disparado automáticamente\n";
    echo "   Canal WebSocket: estados-departamento\n";
    echo "   Evento: estado-departamento.updated\n";
    echo "   Datos del evento: " . json_encode($estadoDepartamento->toArray()) . "\n\n";
    
    // Esperar un momento
    echo "⏳ Esperando 3 segundos...\n";
    sleep(3);
    
    echo "🔄 Restaurando valores originales...\n";
    
    // Restaurar valores originales
    $estadoDepartamento->update([
        'nombre' => $nombreOriginal,
        'descripcion' => $descripcionOriginal,
        'color' => '#6B7280' // Color gris por defecto
    ]);
    
    echo "✅ Valores restaurados\n";
    echo "   Nombre restaurado: {$estadoDepartamento->nombre}\n";
    echo "   Descripción restaurada: {$estadoDepartamento->descripcion}\n\n";
    
    echo "📡 Segundo evento EstadoDepartamentoUpdated disparado\n\n";
    
    echo "🎉 Prueba completada exitosamente\n";
    echo "💡 Verifica en la interfaz web que la tabla se haya actualizado automáticamente\n";
    echo "💡 Revisa los logs del servidor WebSocket para confirmar la transmisión\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== Fin de la prueba ===\n";
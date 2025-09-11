<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Edificio;
use App\Events\EdificioUpdated;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Crear la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Prueba de WebSocket para Edificios ===\n";
echo "Iniciando prueba de actualización de edificio...\n\n";

// Buscar un edificio existente o crear uno de prueba
$edificio = Edificio::first();

if (!$edificio) {
    echo "No se encontraron edificios. Creando uno de prueba...\n";
    $edificio = Edificio::create([
        'nombre' => 'Edificio de Prueba WebSocket',
        'direccion' => 'Dirección de Prueba',
        'pisos' => 5,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Edificio creado con ID: {$edificio->id}\n\n";
}

echo "Edificio seleccionado: ID {$edificio->id} - {$edificio->nombre}\n";
echo "Valores originales:\n";
echo "- Nombre: {$edificio->nombre}\n";
echo "- Dirección: {$edificio->direccion}\n";
echo "- Pisos: {$edificio->pisos}\n\n";

// Guardar valores originales
$nombreOriginal = $edificio->nombre;
$direccionOriginal = $edificio->direccion;
$pisosOriginal = $edificio->pisos;

// Actualizar el edificio
echo "Actualizando edificio...\n";
$edificio->update([
    'nombre' => 'Edificio Actualizado WebSocket - ' . date('H:i:s'),
    'direccion' => 'Nueva Dirección - ' . date('Y-m-d H:i:s'),
    'pisos' => $pisosOriginal + 1
]);

echo "Edificio actualizado exitosamente.\n";
echo "Nuevos valores:\n";
echo "- Nombre: {$edificio->nombre}\n";
echo "- Dirección: {$edificio->direccion}\n";
echo "- Pisos: {$edificio->pisos}\n\n";

// Disparar evento WebSocket manualmente
echo "Disparando evento EdificioUpdated...\n";
event(new EdificioUpdated($edificio));
echo "Evento EdificioUpdated disparado.\n\n";

// Esperar un momento
echo "Esperando 2 segundos...\n";
sleep(2);

// Restaurar valores originales
echo "Restaurando valores originales...\n";
$edificio->update([
    'nombre' => $nombreOriginal,
    'direccion' => $direccionOriginal,
    'pisos' => $pisosOriginal
]);

echo "Valores restaurados exitosamente.\n";
echo "Prueba completada.\n\n";
echo "=== Instrucciones ===\n";
echo "1. Verifica la interfaz web en: http://localhost:8000/admin/edificios\n";
echo "2. Revisa los logs del servidor WebSocket para confirmar que se enviaron los eventos\n";
echo "3. Confirma que las notificaciones aparecieron en la interfaz\n";
echo "4. Verifica que la tabla se actualizó automáticamente\n";
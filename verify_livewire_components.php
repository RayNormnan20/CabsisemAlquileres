<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACIÓN DE COMPONENTES LIVEWIRE ===\n";

// Verificar si Livewire está instalado
if (class_exists('Livewire\Livewire')) {
    echo "✅ Livewire está instalado\n";
} else {
    echo "❌ Livewire NO está instalado\n";
    exit(1);
}

// Verificar componentes Livewire relacionados con abonos
$livewireComponents = [
    'App\\Livewire\\AbonosTable',
    'App\\Http\\Livewire\\AbonosTable',
    'App\\Filament\\Resources\\AbonosResource\\Pages\\ListAbonos'
];

echo "\n=== COMPONENTES LIVEWIRE ===\n";
foreach ($livewireComponents as $component) {
    if (class_exists($component)) {
        echo "✅ Componente encontrado: {$component}\n";
    } else {
        echo "❌ Componente NO encontrado: {$component}\n";
    }
}

// Verificar eventos Livewire
echo "\n=== EVENTOS LIVEWIRE DISPONIBLES ===\n";
echo "Los siguientes eventos deberían estar disponibles en el frontend:\n";
echo "- refreshComponent\n";
echo "- \$refresh\n";
echo "- Filament.table.refresh\n";

// Verificar configuración de broadcasting
echo "\n=== CONFIGURACIÓN DE BROADCASTING ===\n";
$broadcastDriver = config('broadcasting.default');
echo "Driver de broadcasting: {$broadcastDriver}\n";

if ($broadcastDriver === 'pusher') {
    echo "✅ Broadcasting configurado correctamente\n";
} else {
    echo "⚠️  Broadcasting no está usando Pusher\n";
}

echo "\n=== RECOMENDACIONES ===\n";
echo "1. Asegúrate de que las pestañas estén en la misma ruta (ID: 2)\n";
echo "2. Verifica que los WebSocket listeners estén activos\n";
echo "3. Comprueba la consola del navegador para errores de JavaScript\n";
echo "4. El evento 'abono.created' debería disparar actualizaciones Livewire\n";

echo "\n=== PRUEBA COMPLETADA ===\n";
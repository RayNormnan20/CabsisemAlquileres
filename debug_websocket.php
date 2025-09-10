<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Abonos;
use Illuminate\Support\Facades\Session;

echo "=== DEBUG WEBSOCKET CHANNELS ===\n";

// Simular ruta seleccionada
$rutaSeleccionada = 2; // Simular ruta 2

echo "Ruta simulada: " . $rutaSeleccionada . "\n";

// Obtener el último abono creado
$ultimoAbono = Abonos::with(['cliente', 'ruta'])->latest('created_at')->first();

if ($ultimoAbono) {
    echo "\n=== ÚLTIMO ABONO CREADO ===\n";
    echo "ID Abono: " . $ultimoAbono->id_abono . "\n";
    echo "ID Ruta del abono: " . $ultimoAbono->id_ruta . "\n";
    echo "Cliente: " . ($ultimoAbono->cliente ? $ultimoAbono->cliente->nombre : 'N/A') . "\n";
    echo "Ruta: " . ($ultimoAbono->ruta ? $ultimoAbono->ruta->nombre : 'N/A') . "\n";
    echo "Fecha creación: " . $ultimoAbono->created_at . "\n";
    
    echo "\n=== CANALES WEBSOCKET ===\n";
    echo "Canal del evento AbonoCreated: ruta." . $ultimoAbono->id_ruta . "\n";
    echo "Canal que escucha JavaScript: ruta." . $rutaSeleccionada . "\n";
    
    if ($ultimoAbono->id_ruta == $rutaSeleccionada) {
        echo "✅ LOS CANALES COINCIDEN\n";
    } else {
        echo "❌ LOS CANALES NO COINCIDEN\n";
        echo "Problema: El abono se creó en ruta {$ultimoAbono->id_ruta} pero JavaScript escucha ruta " . $rutaSeleccionada . "\n";
    }
} else {
    echo "No se encontraron abonos en la base de datos\n";
}

echo "\n=== VERIFICAR ESTRUCTURA ABONOS ===\n";
$abonos = Abonos::select('id_abono', 'id_ruta', 'id_cliente', 'created_at')
    ->latest('created_at')
    ->limit(5)
    ->get();

foreach ($abonos as $abono) {
    echo "Abono {$abono->id_abono}: Ruta {$abono->id_ruta}, Cliente {$abono->id_cliente}, Creado: {$abono->created_at}\n";
}

echo "\n=== FIN DEBUG ===\n";
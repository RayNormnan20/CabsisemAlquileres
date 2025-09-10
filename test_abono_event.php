<?php

require_once 'vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Events\AbonoCreated;
use App\Models\Abonos;

echo "Creando abono de prueba..." . PHP_EOL;

// Crear un abono de prueba usando el modelo real
$abono = new Abonos();
$abono->id = 999;
$abono->monto = 100.50;
$abono->id_credito = 1;
$abono->fecha = date('Y-m-d');
$abono->observaciones = 'Abono de prueba para WebSocket';

// Disparar el evento
event(new AbonoCreated($abono));

echo "Evento AbonoCreated enviado correctamente." . PHP_EOL;
echo "Verifica en el WebSocket server si el evento fue recibido." . PHP_EOL;
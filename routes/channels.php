<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Ruta;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para rutas - solo usuarios con acceso a la ruta pueden escuchar
Broadcast::channel('ruta.{rutaId}', function ($user, $rutaId) {
    // Super Admin tiene acceso a todas las rutas
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    $ruta = Ruta::find($rutaId);
    if (!$ruta) {
        return false;
    }
    
    // Administrador y Encargado de oficina: acceso si es de su oficina
    if ($user->hasAnyRole(['Administrador', 'Encargado de oficina'])) {
        return $ruta->id_oficina == $user->id_oficina;
    }
    
    // Cobrador: solo rutas asignadas
    if ($user->hasRole('Cobrador')) {
        return $user->rutas()->where('id_ruta', $rutaId)->exists();
    }
    
    // Revisador: rutas que puede revisar
    if ($user->hasRole('Revisador')) {
        return $user->rutasRevisables()->where('id_ruta', $rutaId)->exists();
    }
    
    return false;
});

// Canal para eventos de clientes - acceso basado en ruta
Broadcast::channel('clientes.{rutaId}', function ($user, $rutaId) {
    // Super Admin tiene acceso a todas las rutas
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    $ruta = Ruta::find($rutaId);
    if (!$ruta) {
        return false;
    }
    
    // Administrador y Encargado de oficina: acceso si es de su oficina
    if ($user->hasAnyRole(['Administrador', 'Encargado de oficina'])) {
        return $ruta->id_oficina == $user->id_oficina;
    }
    
    // Cobrador: solo rutas asignadas
    if ($user->hasRole('Cobrador')) {
        return $user->rutas()->where('id_ruta', $rutaId)->exists();
    }
    
    // Revisador: rutas que puede revisar
    if ($user->hasRole('Revisador')) {
        return $user->rutasRevisables()->where('id_ruta', $rutaId)->exists();
    }
    
    return false;
});

// Canal para eventos de créditos - acceso basado en ruta
Broadcast::channel('creditos.{rutaId}', function ($user, $rutaId) {
    // Super Admin tiene acceso a todas las rutas
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    $ruta = Ruta::find($rutaId);
    if (!$ruta) {
        return false;
    }
    
    // Administrador y Encargado de oficina: acceso si es de su oficina
    if ($user->hasAnyRole(['Administrador', 'Encargado de oficina'])) {
        return $ruta->id_oficina == $user->id_oficina;
    }
    
    // Cobrador: solo rutas asignadas
    if ($user->hasRole('Cobrador')) {
        return $user->rutas()->where('id_ruta', $rutaId)->exists();
    }
    
    // Revisador: rutas que puede revisar
    if ($user->hasRole('Revisador')) {
        return $user->rutasRevisables()->where('id_ruta', $rutaId)->exists();
    }
    
    return false;
});

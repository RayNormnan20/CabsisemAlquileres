<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;
use App\Helpers\RutaPermissionHelper;

class FilamentServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Filament::serving(function () {
            // Registrar los grupos de navegación
            Filament::registerNavigationGroups([
                'Principal',
                'Créditos',
                'Movimientos',
                'Créditos',
                'Alquileres',        // Agregado primero
                'Movimientos',       // Segundo
                'Permissions',
                'Clientes'
            ]);

            // Eliminados ítems de navegación para Liquidaciones cruce créditos/abonos y Trasladar/Reportes basados en créditos
        });
    }
}

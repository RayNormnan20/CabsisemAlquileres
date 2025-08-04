<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

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
                'Principal > Reportes',
                'Principal > Reportes > Recaudador',
                'Permissions',
                'Movimientos',
                'Clientes'
            ]);
        });
    }
}
<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;
use App\Filament\Pages\ClienteCreditosAbonos;
use App\Filament\Pages\TrasladarClientes;

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
            
            // Registrar navegación condicional para Liquidaciones
            if (auth()->check() && auth()->user()->can('Listar Liquidaciones')) {
                Filament::registerNavigationItems([
                    NavigationItem::make('Liquidaciones')
                        ->url(ClienteCreditosAbonos::getUrl())
                        ->icon('heroicon-o-document-report')
                        ->group('Movimientos')
                        ->sort(4)
                ]);
            }
            
            // Registrar navegación condicional para Trasladar Clientes
            if (auth()->check() && auth()->user()->can('Listar Trasladar Clientes')) {
                Filament::registerNavigationItems([
                    NavigationItem::make('Trasladar Clientes')
                        ->url(TrasladarClientes::getUrl())
                        ->icon('heroicon-o-switch-horizontal')
                        ->group('Movimientos')
                        ->sort(4)
                ]);
            }
        });
    }
}
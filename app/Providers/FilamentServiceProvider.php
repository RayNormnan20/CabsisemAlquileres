<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;
use App\Filament\Pages\ClienteCreditosAbonos;
use App\Filament\Pages\TrasladarClientes;
use App\Filament\Pages\ReportesCristian;

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
                        ->isActiveWhen(fn (): bool => request()->url() === url(ClienteCreditosAbonos::getUrl()))
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
                        ->isActiveWhen(fn (): bool => request()->url() === url(TrasladarClientes::getUrl()))
                        ->icon('heroicon-o-switch-horizontal')
                        ->group('Movimientos')
                        ->sort(4)
                ]);
            }
            
            // Registrar navegación condicional para Reportes Cristian
            if (auth()->check() && auth()->user()->can('Ver Reportes Cristian')) {
                Filament::registerNavigationItems([
                    NavigationItem::make('Reportes Cristian')
                        ->url(ReportesCristian::getUrl())
                        ->isActiveWhen(fn (): bool => request()->url() === url(ReportesCristian::getUrl()))
                        ->icon('heroicon-o-chart-bar')
                        ->group('Movimientos')
                        ->sort(5)
                ]);
            }
        });
    }
}
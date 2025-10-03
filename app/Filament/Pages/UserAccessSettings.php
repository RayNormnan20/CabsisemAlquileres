<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\UserAccessHoursWidget;
use Filament\Pages\Page;

class UserAccessSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Horarios de acceso';
    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $slug = 'configuracion/horarios-acceso';
    protected static string $view = 'filament.pages.user-access-settings';

    public static function shouldRegisterNavigation(): bool
    {
        // Solo mostrar en navegación para usuarios con rol "Administrador"
        return auth()->check() && auth()->user()->hasRole('Administrador');
    }

    public function mount(): void
    {
        // Restringir acceso directo a la página: solo "Administrador"
        if (! auth()->check() || ! auth()->user()->hasRole('Administrador')) {
            abort(403);
        }
    }

    protected function getHeading(): string
    {
        return 'Configuración de horarios de acceso';
    }

    protected function getWidgets(): array
    {
        return [
            UserAccessHoursWidget::class,
        ];
    }
}

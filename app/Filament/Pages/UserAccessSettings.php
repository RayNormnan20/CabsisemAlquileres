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
        return auth()->check() && auth()->user()->can('Manage general settings');
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
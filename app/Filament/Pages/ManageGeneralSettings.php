<?php

namespace App\Filament\Pages;

use App\Models\Role;
use App\Models\LogActividad;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageGeneralSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $settings = GeneralSettings::class;

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('Manage general settings');
    }

    protected function getHeading(): string|Htmlable
    {
        return __('Manage general settings');
    }

    protected static function getNavigationLabel(): string
    {
        return __('General');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Configuración');

    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            FileUpload::make('site_logo')
                                ->label(__('Site logo'))
                                ->helperText(__('This is the platform logo (e.g. Used in site favicon)'))
                                ->image()
                                ->columnSpan(1)
                                ->maxSize(config('system.max_file_size')),

                            Grid::make(1)
                                ->columnSpan(2)
                                ->schema([
                                    TextInput::make('site_name')
                                        ->label(__('Site name'))
                                        ->helperText(__('This is the platform name'))
                                        ->default(fn() => config('app.name'))
                                        ->required(),

                                    // Toggle::make('enable_registration')
                                    //     ->label(__('Enable registration?'))
                                    //     ->helperText(__('If enabled, any user can create an account in this platform. But an administration need to give them permissions.')),

                                    // Toggle::make('enable_social_login')
                                    //     ->label(__('Enable social login?'))
                                    //     ->helperText(__('If enabled, configured users can login via their social accounts.')),

                                    // Toggle::make('enable_login_form')
                                    //     ->label(__('Enable form login?'))
                                    //     ->helperText(__('If enabled, a login form will be visible on the login page.')),

                                    // Toggle::make('enable_oidc_login')
                                    //     ->label(__('Enable OIDC login?'))
                                    //     ->helperText(__('If enabled, an OIDC Connect button will be visible on the login page.')),

                                    Toggle::make('enable_yape_filter')
                                        ->label('Habilitar filtro de Yape en abonos')
                                        ->helperText('Si está habilitado, se mostrará la opción "Mostrar solo nombres Yape completados o en exceso" en el formulario de abonos.'),

                                    Toggle::make('enable_devolucion_filter')
                                        ->label('Habilitar opción de devolución')
                                        ->helperText('Si está habilitado, se mostrará la opción "Es Devolución" en el formulario de abonos para marcar registros como devolución.'),

                                    Toggle::make('enable_segundo_recorrido_filter')
                                        ->label('Mostrar campo "Activar Segundo Recorrido"')
                                        ->helperText('Controla la visibilidad del checkbox "Activar Segundo Recorrido" en el formulario de abonos'),

                                        Toggle::make('enable_renovacion_filter')
                                        ->label('Habilitar opción de renovación')
                                        ->helperText('Si está habilitado, se mostrará la opción "Habilitar Renovación" en la planilla del recaudador para créditos vencidos.'),

                                    Toggle::make('mostrar_porcentaje_interes')
                                        ->label('Mostrar columna Porcentaje de Interés')
                                        ->helperText('Si está habilitado, se mostrará la columna "Porcentaje de Interés" en la tabla de créditos (solo para Administradores).'),

                                    Toggle::make('mostrar_tipo_pago')
                                        ->label('Mostrar columna Tipo de Pago')
                                        ->helperText('Si está habilitado, se mostrará la columna "Tipo" en la tabla de créditos (solo para Administradores).'),

                                    Toggle::make('mostrar_numero_cuotas')
                                        ->label('Mostrar columna Número de Cuotas')
                                        ->helperText('Si está habilitado, se mostrará la columna "Nr. cuotas" en la tabla de créditos (solo para Administradores).'),

                                    Toggle::make('mostrar_usuario_creador')
                                        ->label('Mostrar columna Usuario Creador')
                                        ->helperText('Si está habilitado, se mostrará la columna "Creado por" en la tabla de créditos (solo para Administradores).'),

                                    Select::make('site_language')
                                        ->label(__('Site language'))
                                        ->helperText(__('The language used by the platform.'))
                                        ->searchable()
                                        ->options($this->getLanguages()),

                                    Select::make('default_role')
                                        ->label(__('Default role'))
                                        ->helperText(__('The platform default role (used mainly in OIDC Connect).'))
                                        ->searchable()
                                        ->options(Role::all()->pluck('name', 'id')->toArray()),
                                ]),
                        ]),
                ]),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label(__('Guadar'));
    }

    private function getLanguages(): array
    {
        $languages = config('system.locales.list');
        asort($languages);
        return $languages;
    }
}

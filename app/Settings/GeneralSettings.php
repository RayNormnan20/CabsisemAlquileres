<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    public string $site_name;
    public bool $enable_registration;
    public string|null $site_logo;
    public string|null $enable_social_login;
    public string|null $site_language;
    public string|null $default_role;
    public string|null $enable_login_form;
    public string|null $enable_oidc_login;
    public bool $enable_yape_filter;
    public bool $enable_devolucion_filter;
    public bool $enable_renovacion_filter;

    public bool $enable_segundo_recorrido_filter;

    public bool $mostrar_porcentaje_interes ;
    public bool $mostrar_tipo_pago ;
    public bool $mostrar_numero_cuotas;
    public bool $mostrar_usuario_creador;

    public static function group(): string
    {
        return 'general';
    }

}

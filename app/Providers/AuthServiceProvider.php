<?php

namespace App\Providers;

// Eliminado: TrasladarClientes page
use App\Filament\Resources\ReportesResource\Pages\PlanillaRecaudador;
use App\Models\Alquiler;
use App\Models\ClienteAlquiler;
use App\Models\Concepto;
use App\Models\Departamento;
use App\Models\EstadoDepartamento;
use App\Models\Edificio;
use App\Models\Movimiento;
use App\Models\Oficina;
use App\Models\PagoAlquiler;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Ruta;
// Eliminados: modelos de créditos/abonos/clientes/días no laborables/YapeCliente
use App\Policies\AlquilerPolicy;
use App\Policies\ClienteAlquilerPolicy;
use App\Policies\LiquidacionPolicy;
use App\Policies\ConceptoPolicy;
use App\Policies\DepartamentoPolicy;
use App\Policies\EstadoDepartamentoPolicy;
use App\Policies\EdificioPolicy;
use App\Policies\MovimientoPolicy;
use App\Policies\OficinaPolicy;
use App\Policies\PagoAlquilerPolicy;
use App\Policies\PlanillaRecaudadorPolicy;
use App\Policies\ReportesCristianoPolicy;
use App\Policies\RutaPolicy;
use App\Policies\TrasladarClientePolicy;
use Filament\Tables\Filters\TrashedFilter;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
            Alquiler::class => AlquilerPolicy::class,
            ClienteAlquiler::class => ClienteAlquilerPolicy::class,
            Concepto::class => ConceptoPolicy::class,
            Departamento::class => DepartamentoPolicy::class,
            EstadoDepartamento::class => EstadoDepartamentoPolicy::class,
            Edificio::class => EdificioPolicy::class,
            Movimiento::class => MovimientoPolicy::class,
            'Liquidacion' => LiquidacionPolicy::class,
            Oficina::class => OficinaPolicy::class,
            PagoAlquiler::class => PagoAlquilerPolicy::class,
            PlanillaRecaudador::class => PlanillaRecaudadorPolicy::class,
            'ReportesCristian' => ReportesCristianoPolicy::class,
            Ruta::class => RutaPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Definición de gates (permisos) para cada rol
        $this->defineRolesGates();

        // Definición de gates específicos para rutas
        $this->defineRutaGates();
    }

    protected function defineRolesGates()
    {
        // Super Admin tiene todos los permisos
        Gate::before(function (User $user) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });

        // Permisos para Administrador
        Gate::define('manage-system', function (User $user) {
            return $user->hasRole('Administrador');
        });

        // Permisos para Encargado de oficina
        Gate::define('manage-office', function (User $user) {
            return $user->hasRole('Encargado de oficina');
        });

        // Permisos para Cobrador
        Gate::define('collect-payments', function (User $user) {
            return $user->hasRole('Cobrador');
        });

        // Permisos para Revisador
        Gate::define('review-data', function (User $user) {
            return $user->hasRole('Revisador');
        });
    }

    protected function defineRutaGates()
    {
        // Acceso a una ruta específica
        Gate::define('access-ruta', function (User $user, Ruta $ruta) {
            // Super Admin y Administrador tienen acceso completo
            if ($user->hasAnyRole(['Super Admin', 'Administrador'])) {
                return true;
            }

            // Encargado de oficina solo a rutas de su oficina
            if ($user->hasRole('Encargado de oficina')) {
                return $user->oficina && $user->oficina->id_oficina === $ruta->id_oficina;
            }

            // Cobrador solo a sus rutas asignadas
            if ($user->hasRole('Cobrador')) {
                return $user->rutas()->where('ruta.id_ruta', $ruta->id_ruta)->exists();
            }

            // Revisador según configuración específica
            if ($user->hasRole('Revisador')) {
                return $user->rutasRevisables()->where('ruta.id_ruta', $ruta->id_ruta)->exists();
            }

            return false;
        });

        // Crear/editar rutas
        Gate::define('manage-rutas', function (User $user) {
            return $user->hasAnyRole(['Super Admin', 'Administrador', 'Encargado de oficina']);
        });

        // Ver reportes de rutas
        Gate::define('view-ruta-reports', function (User $user) {
            return $user->hasAnyRole(['Super Admin', 'Administrador', 'Encargado de oficina', 'Revisador']);
        });
    }
}

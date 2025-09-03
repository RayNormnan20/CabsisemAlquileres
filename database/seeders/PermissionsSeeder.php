<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    private array $modules = [
        'Permisos', 'Rol', 'Usuario', 'Clientes', 'Oficina', 'Ruta',
        'Creditos', 'Abonos', 'Concepto', 'Planilla Recaudador', 'YapeCliente',
        'Alquiler', 'Pagos Alquiler', 'Cliente Alquiler', 'Departamento', 'Estado Departamento',
        'Edificio', 'Concepto Abono', 'Dia No Laborable', 'Movimiento', 'Liquidaciones', 'Trasladar Clientes', 'Clientes Por Renovar',
        'Yapes Totales Del Dia', 'Segundo Recorrido', 'Usuarios Que Abonaron A Yape', 'Yape Clientes Control De Entregas'
    ];

    // Módulos que solo deben tener permisos de 'Listar'
    private array $listOnlyModules = [
        'Liquidaciones', 'Trasladar Clientes', 'Clientes Por Renovar',
        'Yapes Totales Del Dia', 'Segundo Recorrido', 'Usuarios Que Abonaron A Yape',
        'Yape Clientes Control De Entregas', 'Planilla Recaudador'
    ];

    private array $pluralActions = [
        'Listar'
    ];

    private array $singularActions = [
        'Ver', 'Crear', 'Actualizar', 'Eliminar'
    ];

    private array $extraPermissions = [
        'Manage general settings', 'Import from Jira',
        'List timesheet data', 'View timesheet dashboard'
    ];

    private string $defaultRole = 'Administrador';

    public function run()
    {
        // Crear todos los permisos básicos
        foreach ($this->modules as $module) {
            $plural = Str::plural($module);

            foreach ($this->pluralActions as $action) {
                Permission::firstOrCreate(['name' => "$action $plural"]);
            }

            // Solo crear permisos singulares si el módulo no está en la lista de solo-listar
            if (!in_array($module, $this->listOnlyModules)) {
                foreach ($this->singularActions as $action) {
                    Permission::firstOrCreate(['name' => "$action $module"]);
                }
            }
        }

        // Crear permisos adicionales
        foreach ($this->extraPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Rol Administrador (todos los permisos)
        $adminRole = Role::firstOrCreate(['name' => $this->defaultRole]);
        $adminRole->syncPermissions(Permission::all()->pluck('name'));

        // Configurar rol por defecto (forma corregida)
        $settings = app(GeneralSettings::class);
        $settings->default_role = $adminRole->id;
        $settings->save();

        // Rol Cobrador con permisos específicos
        $collectorRole = Role::firstOrCreate(['name' => 'Cobrador']);
        $collectorPermissions = [
            // Clientes
            'Listar Clientes', 'Ver Clientes', 'Actualizar Clientes', 'Eliminar Clientes', 'Crear Clientes',

            // Créditos
            'Listar Creditos', 'Ver Creditos', 'Crear Creditos', 'Actualizar Creditos', 'Eliminar Creditos',

            // Abonos
            'Listar Abonos', 'Ver Abonos', 'Crear Abonos', 'Actualizar Abonos', 'Eliminar Abonos',

            // YapeCliente
            'Listar YapeClientes', 'Ver YapeCliente', 'Crear YapeCliente', 'Actualizar YapeCliente', 'Eliminar YapeCliente',
        ];

        $collectorRole->syncPermissions($collectorPermissions);

        // Rol Encargado de oficina (sin permisos específicos por ahora)
        $officeManagerRole = Role::firstOrCreate(['name' => 'Encargado de oficina']);
        // TODO: Asignar permisos específicos para Encargado de oficina

        // Rol Revisador (sin permisos específicos por ahora)
        $reviewerRole = Role::firstOrCreate(['name' => 'Revisador']);
        // TODO: Asignar permisos específicos para Revisador

        // Rol Super Admin (todos los permisos)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->syncPermissions(Permission::all()->pluck('name'));

        // Asignar rol admin al primer usuario si existe
        if ($user = User::first()) {
            $user->syncRoles([$this->defaultRole]);
        }
    }
}

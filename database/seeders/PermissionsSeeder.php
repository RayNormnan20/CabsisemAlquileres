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
        'Edificio'
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

            foreach ($this->singularActions as $action) {
                Permission::firstOrCreate(['name' => "$action $module"]);
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

        // Asignar rol admin al primer usuario si existe
        if ($user = User::first()) {
            $user->syncRoles([$this->defaultRole]);
        }
    }
}
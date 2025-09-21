<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddMostrarUsuarioCreadorToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.mostrar_usuario_creador', false);
    }

    public function down(): void
    {
        $this->migrator->delete('general.mostrar_usuario_creador');
    }
}
<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

class AddMostrarUsuarioCreadorToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.mostrar_usuario_creador', false);
        } catch (SettingAlreadyExists $e) {
            // Setting already exists
        }
    }

    public function down(): void
    {
        try {
            $this->migrator->delete('general.mostrar_usuario_creador');
        } catch (\Exception $e) {}
    }
}
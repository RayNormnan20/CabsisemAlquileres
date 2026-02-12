<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

class AddCreditosColumnsVisibilityToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.mostrar_porcentaje_interes', false);
        } catch (SettingAlreadyExists $e) {}

        try {
            $this->migrator->add('general.mostrar_tipo_pago', false);
        } catch (SettingAlreadyExists $e) {}

        try {
            $this->migrator->add('general.mostrar_numero_cuotas', false);
        } catch (SettingAlreadyExists $e) {}
    }

    public function down(): void
    {
        try {
            $this->migrator->delete('general.mostrar_porcentaje_interes');
            $this->migrator->delete('general.mostrar_tipo_pago');
            $this->migrator->delete('general.mostrar_numero_cuotas');
        } catch (\Exception $e) {}
    }
}
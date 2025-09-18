<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddCreditosColumnsVisibilityToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.mostrar_porcentaje_interes', false);
        $this->migrator->add('general.mostrar_tipo_pago', false);
        $this->migrator->add('general.mostrar_numero_cuotas', false);
    }

    public function down(): void
    {
        $this->migrator->delete('general.mostrar_porcentaje_interes');
        $this->migrator->delete('general.mostrar_tipo_pago');
        $this->migrator->delete('general.mostrar_numero_cuotas');
    }
}
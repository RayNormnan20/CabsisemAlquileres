<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

class AddDevolucionAndSegundoRecorridoFiltersToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.enable_devolucion_filter', false);
        } catch (SettingAlreadyExists $e) {
        }

        try {
            $this->migrator->add('general.enable_segundo_recorrido_filter', false);
        } catch (SettingAlreadyExists $e) {
        }
    }

    public function down(): void
    {
        try {
            $this->migrator->delete('general.enable_devolucion_filter');
            $this->migrator->delete('general.enable_segundo_recorrido_filter');
        } catch (\Exception $e) {
        }
    }
}


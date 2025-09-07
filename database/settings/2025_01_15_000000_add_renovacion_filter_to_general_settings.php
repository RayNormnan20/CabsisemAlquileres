<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddRenovacionFilterToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.enable_renovacion_filter', false);
    }

    public function down(): void
    {
        $this->migrator->delete('general.enable_renovacion_filter');
    }
}
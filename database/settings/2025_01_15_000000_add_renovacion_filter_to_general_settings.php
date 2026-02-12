<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

class AddRenovacionFilterToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.enable_renovacion_filter', false);
        } catch (SettingAlreadyExists $e) {
            // Setting already exists
        }
    }

    public function down(): void
    {
        try {
            $this->migrator->delete('general.enable_renovacion_filter');
        } catch (\Exception $e) {
            // Setting might not exist
        }
    }
}
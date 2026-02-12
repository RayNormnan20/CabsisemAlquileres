<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

class AddYapeFilterToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        try {
            $this->migrator->add('general.enable_yape_filter', true);
        } catch (SettingAlreadyExists $e) {
            // Setting already exists, do nothing
        }
    }
}
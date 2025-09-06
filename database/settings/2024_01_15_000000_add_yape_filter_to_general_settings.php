<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddYapeFilterToGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.enable_yape_filter', true);
    }
}
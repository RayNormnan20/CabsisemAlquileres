<?php

namespace App\Filament\Resources\DiasNoLaborablesResource\Pages;

use App\Filament\Resources\DiasNoLaborablesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDiasNoLaborables extends CreateRecord
{
    protected static string $resource = DiasNoLaborablesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Día no laborable creado exitosamente';
    }
}

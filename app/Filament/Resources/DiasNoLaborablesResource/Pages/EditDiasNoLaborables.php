<?php

namespace App\Filament\Resources\DiasNoLaborablesResource\Pages;

use App\Filament\Resources\DiasNoLaborablesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiasNoLaborables extends EditRecord
{
    protected static string $resource = DiasNoLaborablesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Día no laborable actualizado exitosamente';
    }
}

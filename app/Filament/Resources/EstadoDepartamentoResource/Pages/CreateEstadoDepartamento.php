<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoDepartamento extends CreateRecord
{
    protected static string $resource = EstadoDepartamentoResource::class;
     protected function getCreatedNotificationTitle(): ?string
    {
        return 'Estado de departamento creado exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}

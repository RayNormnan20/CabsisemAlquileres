<?php

namespace App\Filament\Resources\YapeClienteResource\Pages;

use App\Filament\Resources\YapeClienteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateYapeCliente extends CreateRecord
{
    protected static string $resource = YapeClienteResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Resources\YapeClienteResource\Pages;

use App\Filament\Resources\YapeClienteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYapeCliente extends EditRecord
{
    protected static string $resource = YapeClienteResource::class;

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
}

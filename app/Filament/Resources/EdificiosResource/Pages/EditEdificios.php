<?php

namespace App\Filament\Resources\EdificiosResource\Pages;

use App\Filament\Resources\EdificiosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEdificios extends EditRecord
{
    protected static string $resource = EdificiosResource::class;

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
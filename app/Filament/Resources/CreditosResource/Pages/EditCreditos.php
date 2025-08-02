<?php

namespace App\Filament\Resources\CreditosResource\Pages;

use App\Filament\Resources\CreditosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditos extends EditRecord
{
    protected static string $resource = CreditosResource::class;

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

<?php

namespace App\Filament\Resources\DepartamentosResource\Pages;

use App\Filament\Resources\DepartamentosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartamentos extends EditRecord
{
    protected static string $resource = DepartamentosResource::class;

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
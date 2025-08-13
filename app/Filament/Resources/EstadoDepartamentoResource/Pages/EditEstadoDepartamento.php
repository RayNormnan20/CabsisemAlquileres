<?php

namespace App\Filament\Resources\EstadoDepartamentoResource\Pages;

use App\Filament\Resources\EstadoDepartamentoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstadoDepartamento extends EditRecord
{
    protected static string $resource = EstadoDepartamentoResource::class;

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

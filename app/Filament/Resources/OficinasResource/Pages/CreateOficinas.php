<?php

namespace App\Filament\Resources\OficinasResource\Pages;

use App\Filament\Resources\OficinasResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOficinas extends CreateRecord
{
    protected static string $resource = OficinasResource::class;

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver')
                ->url(static::$resource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('secondary')
                ->button(),
        ];
    }
}

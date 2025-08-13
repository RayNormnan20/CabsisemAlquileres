<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlquileres extends EditRecord
{
    protected static string $resource = AlquileresResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

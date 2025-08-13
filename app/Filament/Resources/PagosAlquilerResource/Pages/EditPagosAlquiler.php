<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagosAlquiler extends EditRecord
{
    protected static string $resource = PagosAlquilerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

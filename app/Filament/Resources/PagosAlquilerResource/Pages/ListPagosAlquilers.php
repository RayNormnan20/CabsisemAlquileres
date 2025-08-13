<?php

namespace App\Filament\Resources\PagosAlquilerResource\Pages;

use App\Filament\Resources\PagosAlquilerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagosAlquilers extends ListRecords
{
    protected static string $resource = PagosAlquilerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

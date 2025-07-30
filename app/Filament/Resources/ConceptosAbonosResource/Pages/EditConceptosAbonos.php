<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConceptosAbonos extends EditRecord
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
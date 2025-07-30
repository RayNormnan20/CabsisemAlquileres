<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Models\ConceptoAbono;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateConceptosAbonos extends CreateRecord
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario'] = Auth::id(); // Registrar el usuario actual
       // $data['tipo_concepto'] = request()->get('tipo'); // Tipo por parámetro si viene
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
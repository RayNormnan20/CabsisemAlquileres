<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Models\ConceptoAbono;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreateConceptosAbonos extends CreateRecord
{
    protected static string $resource = ConceptosAbonosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Registrar id_usuario para todos los tipos de concepto
        $data['id_usuario'] = Auth::id();
        
        // Asignar la ruta de la sesión
        $rutaSesion = Session::get('selected_ruta_id');
        if ($rutaSesion) {
            $data['id_ruta'] = $rutaSesion;
        }
        
       // $data['tipo_concepto'] = request()->get('tipo'); // Tipo por parámetro si viene
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
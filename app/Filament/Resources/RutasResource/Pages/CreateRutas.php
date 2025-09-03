<?php

namespace App\Filament\Resources\RutasResource\Pages;

use App\Filament\Resources\RutasResource;
use App\Models\LogActividad; // Importar el modelo LogActividad
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRutas extends CreateRecord
{
    protected static string $resource = RutasResource::class;


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Ruta creada exitosamente';
    }

    protected function afterCreate(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Rutas',
            'Registró una nueva ruta',
            [
                'ruta_id' => $this->record->id_ruta,
                'codigo' => $this->record->codigo,
                'nombre' => $this->record->nombre,
                'oficina' => $this->record->oficina?->nombre ?? 'Sin oficina',
                'activa' => $this->record->activa ? 'Sí' : 'No'
            ]
        );
    }
/*
    protected function getActions(): array
    {
        return [
            Actions\Action::make('cancelar')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }  */
}

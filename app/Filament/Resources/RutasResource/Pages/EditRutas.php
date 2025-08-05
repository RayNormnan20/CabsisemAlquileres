<?php

namespace App\Filament\Resources\RutasResource\Pages;

use App\Filament\Resources\RutasResource;
use App\Models\LogActividad; // Importar el modelo LogActividad
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRutas extends EditRecord
{
    protected static string $resource = RutasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Ruta actualizada exitosamente';
    }

    protected function afterSave(): void
    {
        // Registrar la actividad en el log
        LogActividad::registrar(
            'Rutas',
            'Editó una ruta',
            [
                'ruta_id' => $this->record->id_ruta,
                'codigo' => $this->record->codigo,
                'nombre' => $this->record->nombre,
                'oficina' => $this->record->oficina?->nombre ?? 'Sin oficina',
                'activa' => $this->record->activa ? 'Sí' : 'No'
            ]
        );
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Ruta')
                ->icon('heroicon-s-trash')
                ->after(function () {
                    // Registrar la actividad de eliminación en el log
                    LogActividad::registrar(
                        'Rutas',
                        'Eliminó una ruta',
                        [
                            'ruta_id' => $this->record->id_ruta,
                            'codigo' => $this->record->codigo,
                            'nombre' => $this->record->nombre,
                            'oficina' => $this->record->oficina?->nombre ?? 'Sin oficina'
                        ]
                    );
                }),

            Actions\Action::make('ver_listado')
                ->label('Ver Listado')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary')
                ->icon('heroicon-s-menu-alt-2')
        ];
    }
}
<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
           // Actions\ViewAction::make(),
          //  Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Leer directamente del estado Livewire para incluir cambios hechos desde la vista personalizada
        $permissions = data_get($this, 'data.permissions', []);
        // Sincroniza permisos seleccionados explícitamente, asegurando que se registren en el rol
        $this->record->permissions()->sync($permissions);
    }
}

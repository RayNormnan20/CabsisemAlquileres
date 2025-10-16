<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        // Leer directamente del estado Livewire para incluir cambios hechos desde la vista personalizada
        $permissions = data_get($this, 'data.permissions', []);
        // Registrar permisos seleccionados al crear el rol
        $this->record->permissions()->sync($permissions);
    }

    // Alias para que el botón "Guardar" en la vista personalizada funcione también en Create
    public function save()
    {
        return $this->create();
    }
}

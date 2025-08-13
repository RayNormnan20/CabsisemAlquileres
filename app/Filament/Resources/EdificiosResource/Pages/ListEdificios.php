<?php

namespace App\Filament\Resources\EdificiosResource\Pages;

use App\Filament\Resources\EdificiosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListEdificios extends ListRecords
{
    protected static string $resource = EdificiosResource::class;

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    protected $listeners = ['globalRouteChanged' => 'applyRouteFilter'];

    public function mount(): void
    {
        parent::mount();

        if (Session::has('selected_ruta_id')) {
            $this->currentRutaId = Session::get('selected_ruta_id');
            $this->currentRutaName = Session::get('selected_ruta_name');
        } else {
            $this->currentRutaId = null;
            $this->currentRutaName = 'Todas las Rutas';
        }
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';
        $this->resetPage();
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Agregar el conteo de departamentos
        $query->withCount('departamentos');

        if ($this->currentRutaId) {
            $query->where('id_ruta', $this->currentRutaId);
        }

        $query->where('activo', true);

        return $query;
    }

    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Edificios - {$this->currentRutaName}";
        }
        return "Listado de Edificios";
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Edificio')
                ->icon('heroicon-s-plus'),
        ];
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
}

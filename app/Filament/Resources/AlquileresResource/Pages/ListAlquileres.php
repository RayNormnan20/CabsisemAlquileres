<?php

namespace App\Filament\Resources\AlquileresResource\Pages;

use App\Filament\Resources\AlquileresResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListAlquileres extends ListRecords
{
    protected static string $resource = AlquileresResource::class;

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

        if ($this->currentRutaId) {
            $query->whereHas('inquilino', function($q) {
                $q->where('id_ruta', $this->currentRutaId);
            });
        }

        return $query;
    }

    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Alquileres - {$this->currentRutaName}";
        }
        return "Listado de Alquileres";
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Alquiler')
                ->icon('heroicon-s-plus'),
        ];
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
}

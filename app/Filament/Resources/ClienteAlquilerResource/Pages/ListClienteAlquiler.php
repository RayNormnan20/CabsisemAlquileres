<?php

namespace App\Filament\Resources\ClienteAlquilerResource\Pages;

use App\Filament\Resources\ClienteAlquilerResource;
use App\Filament\Widgets\ClienteAlquilerWebSocketWidget;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListClienteAlquiler extends ListRecords
{
    protected static string $resource = ClienteAlquilerResource::class;
    
    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        'refreshClienteAlquilerTable' => '$refresh',
        '$refresh',
    ];
    
    protected function getHeaderWidgets(): array
    {
        return [
            ClienteAlquilerWebSocketWidget::class,
        ];
    }

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->currentRutaId) {
            $query->where('id_ruta', $this->currentRutaId);
        }

        $query->where('activo', true);

        return $query;
    }

    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Clientes Alquiler";
        }
        return "Listado de Clientes de Alquiler";
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Cliente de Alquiler')
                ->icon('heroicon-s-plus'),
        ];
    }
}


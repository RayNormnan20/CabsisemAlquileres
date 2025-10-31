<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use App\Http\Livewire\Traits\RouteValidation;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListClientes extends ListRecords
{
    use RouteValidation;
    protected static string $resource = ClientesResource::class;

    public ?int $currentRutaId = null;
    public ?string $currentRutaName = null;

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        'refreshClientesTable' => '$refresh',
        '$refresh'
    ];

    public function mount(): void
    {
        parent::mount();
        // Validar y corregir la ruta seleccionada usando el trait
        $this->validateAndCorrectSelectedRoute();

        if (Session::has('selected_ruta_id')) {
            $this->currentRutaId = Session::get('selected_ruta_id');
            $this->currentRutaName = Session::get('selected_ruta_name');
        } else {
            $this->currentRutaId = null;
            $this->currentRutaName = 'Todas las Rutas';
        }
    }

    // Método para aplicar el filtro de ruta cuando se emite el evento
    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        $this->currentRutaId = $rutaId;
        $this->currentRutaName = $rutaName ?? 'Todas las Rutas';

        // Restablece la página de la tabla para aplicar el nuevo filtro
        $this->resetPage();
    }


    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ClientesWebSocketWidget::class,
        ];
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

    // Opcional: Si quieres mostrar el nombre de la ruta actual en la cabecera de la tabla
    protected function getTableHeading(): ?string
    {
        if ($this->currentRutaName && $this->currentRutaId) {
            return "Listado de Clientes (Ruta: " . $this->currentRutaName . ")";
        }
        return "Listado de Clientes";
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Cliente')
                ->icon('heroicon-s-plus'),
        ];
    }
}

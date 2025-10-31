<?php

namespace App\Filament\Resources\ConceptosAbonosResource\Pages;

use App\Filament\Resources\ConceptosAbonosResource;
use App\Http\Livewire\Traits\RouteValidation;
use App\Models\ConceptoAbono;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\ConceptoAbonoWebSocketWidget;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ListConceptosAbonos extends ListRecords
{
    use RouteValidation;
    protected static string $resource = ConceptosAbonosResource::class;

    protected $listeners = [
        'globalRouteChanged' => 'applyRouteFilter',
        'refreshComponent' => '$refresh',
        '$refresh',
    ];

    public function mount(): void
    {
        parent::mount();
        // Validar/corregir la ruta en sesión antes de construir la consulta
        $this->validateAndCorrectSelectedRoute();
    }

    public function applyRouteFilter(?int $rutaId, ?string $rutaName): void
    {
        // Al cambiar la ruta, reiniciar la paginación para reflejar el nuevo filtro
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function getTableQuery(): Builder
    {
        $query = ConceptoAbono::query()
            ->whereNull('id_abono');
            
        $rutaSeleccionada = Session::get('selected_ruta_id');
        if ($rutaSeleccionada) {
            $query->where('id_ruta', $rutaSeleccionada);
        }
        
        return $query;
    }
       protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // Esto oculta el botón "Crear Concepto"
        ];
    }

    // Desactivar completamente acciones masivas y ocultar los checkboxes de selección
    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ConceptoAbonoWebSocketWidget::class,
        ];
    }
}
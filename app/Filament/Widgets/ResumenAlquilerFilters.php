<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Edificio;
use App\Models\Departamento;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;

class ResumenAlquilerFilters extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.resumen-alquiler-filters';
    
    public $selectedEdificio = null;
    public $selectedDepartamento = null;

    protected $listeners = ['refreshFilters' => '$refresh'];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('selectedEdificio')
                        ->label('Edificio')
                        ->placeholder('Seleccionar edificio')
                        ->options($this->getEdificios())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->selectedDepartamento = null;
                            $this->emitUp('updatedSelectedEdificio', $state);
                        }),
                    
                    Select::make('selectedDepartamento')
                        ->label('Departamento')
                        ->placeholder('Seleccionar departamento')
                        ->options($this->getDepartamentos())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->emitUp('updatedSelectedDepartamento', $state);
                        })
                        ->disabled(fn () => !$this->selectedEdificio),
                ])
        ];
    }

    public function getEdificios(): array
    {
        $rutaId = Session::get('selected_ruta_id');
        return Edificio::query()
            ->when($rutaId, fn($q) => $q->where('id_ruta', $rutaId))
            ->orderBy('nombre')
            ->pluck('nombre', 'id_edificio')
            ->toArray();
    }

    public function getDepartamentos(): array
    {
        if (!$this->selectedEdificio) {
            return [];
        }
        $rutaId = Session::get('selected_ruta_id');
        return Departamento::query()
            ->where('id_edificio', $this->selectedEdificio)
            ->when($rutaId, fn($q) => $q->where('id_ruta', $rutaId))
            ->orderBy('numero_departamento')
            ->pluck('numero_departamento', 'id_departamento')
            ->toArray();
    }

    public function updatedSelectedEdificio($value)
    {
        $this->selectedDepartamento = null;
        $this->emitUp('updatedSelectedEdificio', $value);
    }

    public function updatedSelectedDepartamento($value)
    {
        $this->emitUp('updatedSelectedDepartamento', $value);
    }
}
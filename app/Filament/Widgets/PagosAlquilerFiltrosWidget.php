<?php

namespace App\Filament\Widgets;

use App\Models\Edificio;
use App\Models\Departamento;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;

class PagosAlquilerFiltrosWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.pagos-alquiler-filtros-widget';
    protected int | string | array $columnSpan = 'full';

    public $edificioSeleccionado = null;
    public $departamentoSeleccionado = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('edificioSeleccionado')
                        ->label('Edificio')
                        ->placeholder('Seleccionar edificio')
                        ->options($this->getEdificios())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->departamentoSeleccionado = null;
                            $this->emitUp('filtros-edificio-actualizados', [
                                'edificio' => $state,
                                'departamento' => null
                            ]);
                        }),

                    Select::make('departamentoSeleccionado')
                        ->label('Departamento')
                        ->placeholder('Seleccionar departamento')
                        ->options($this->getDepartamentos())
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->emitUp('filtros-edificio-actualizados', [
                                'edificio' => $this->edificioSeleccionado,
                                'departamento' => $state
                            ]);
                        })
                        ->disabled(fn () => !$this->edificioSeleccionado),
                ])
        ];
    }

    protected function getEdificios(): array
    {
        $rutaId = Session::get('selected_ruta_id');
        return Edificio::query()
            ->where('activo', true)
            ->when($rutaId, fn($q) => $q->where('id_ruta', $rutaId))
            ->orderBy('nombre')
            ->pluck('nombre', 'id_edificio')
            ->toArray();
    }

    protected function getDepartamentos(): array
    {
        if (!$this->edificioSeleccionado) {
            return [];
        }
        $rutaId = Session::get('selected_ruta_id');
        return Departamento::query()
            ->where('id_edificio', $this->edificioSeleccionado)
            ->when($rutaId, fn($q) => $q->where('id_ruta', $rutaId))
            ->where('activo', true)
            ->orderBy('numero_departamento')
            ->pluck('numero_departamento', 'id_departamento')
            ->toArray();
    }

    public function updatedEdificioSeleccionado($value)
    {
        $this->departamentoSeleccionado = null;
    }

    public function exportarPdf()
    {
        $this->emitUp('exportar-pdf');
    }
}

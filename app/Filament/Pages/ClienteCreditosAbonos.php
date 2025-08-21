<?php

namespace App\Filament\Pages;

use App\Exports\ClienteCreditosAbonosExport;
use App\Filament\Widgets\ClienteCreditosAbonosWidget;
use App\Models\User;
use App\Models\Ruta;
use Filament\Forms\Components\Actions\Modal\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Log;

class ClienteCreditosAbonos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-report';
    protected static ?string $navigationLabel = 'Liquidaciones';
    protected static ?string $title = 'Liquidaciones por Ruta';
    protected static ?string $slug = 'liquidaciones';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Movimientos';
    
    protected static string $view = 'filament.pages.cliente-creditos-abonos';
    
    public ?int $rutaId = null;
    public $rutas = [];
    
    public function mount(): void
    {
        // Cargar rutas que tienen usuarios asignados
        $this->rutas = Ruta::whereHas('usuarios')
            ->orderBy('nombre')
            ->get()
            ->map(function ($ruta) {
                $usuariosCount = $ruta->usuarios()->count();
                return [
                    'id' => $ruta->id_ruta,
                    'nombre' => $ruta->nombre . ' (' . $usuariosCount . ' usuario' . ($usuariosCount > 1 ? 's' : '') . ')',
                ];
            })
            ->pluck('nombre', 'id')
            ->toArray();
    }
    /*
    protected function getHeaderWidgets(): array
    {
        return [
            ClienteCreditosAbonosWidget::class,
        ];
    }
    */
    public function actualizarRuta(): void
    {
        if ($this->rutaId) {
            $this->emit('ruta-seleccionada', $this->rutaId);
        }
    }
    
    public function handleRutaSeleccionada($rutaId): void
    {
        $this->rutaId = $rutaId;
    }

    public function exportToPDF()
    {
        try {
            if (!$this->rutaId) {
                $this->notify('warning', 'Debe seleccionar una ruta primero');
                return;
            }

            Log::info('Iniciando exportación PDF para ruta: ' . $this->rutaId);

            $export = new ClienteCreditosAbonosExport($this->rutaId, true); // true indica que es por ruta
            return $export->exportToPDF();

        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            $this->notify('danger', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /*
        TODAVIA NO ESTA CONFIGURADO PARA QUE SE PUEDA HACER EXPORTACIONES

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportarPDF')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->hidden(!$this->userId)
                ->url(fn () => route('liquidaciones.export.pdf', ['userId' => $this->userId]))
                ->openUrlInNewTab(),
                
            Action::make('exportarExcel')
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->hidden(!$this->userId)
                ->url(fn () => route('liquidaciones.export.excel', ['userId' => $this->userId]))
                ->openUrlInNewTab(),
        ];
    }

*/
    
    protected function getFormSchema(): array
    {
        return [
            Select::make('rutaId')
                ->label('Ruta')
                ->options($this->rutas)
                ->placeholder('Seleccione una ruta')
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->actualizarRuta();
                }),
        ];
    }
}
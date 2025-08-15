<?php

namespace App\Filament\Pages;

use App\Exports\ClienteCreditosAbonosExport;
use App\Filament\Widgets\ClienteCreditosAbonosWidget;
use App\Models\User;
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
    protected static ?string $title = 'Liquidaciones';
    protected static ?string $slug = 'liquidaciones';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Movimientos';
    
    protected static string $view = 'filament.pages.cliente-creditos-abonos';
    
    public ?int $userId = null;
    public $usuarios = [];
    
    public function mount(): void
    {
        // Cargar usuarios que tienen rutas asignadas
        $this->usuarios = User::whereHas('rutas')
            ->orderBy('name')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
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
    public function actualizarUsuario(): void
    {
        if ($this->userId) {
            $this->emit('usuario-seleccionado', $this->userId);
        }
    }
    
    public function handleUsuarioSeleccionado($userId): void
    {
        $this->userId = $userId;
    }

    public function exportToPDF()
    {
        try {
            if (!$this->userId) {
                $this->notify('warning', 'Debe seleccionar un usuario primero');
                return;
            }

            Log::info('Iniciando exportación PDF para usuario: ' . $this->userId);

            $export = new ClienteCreditosAbonosExport($this->userId);
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
            Select::make('userId')
                ->label('Usuario')
                ->options($this->usuarios)
                ->placeholder('Seleccione un usuario')
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->actualizarUsuario();
                }),
        ];
    }
}
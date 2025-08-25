<?php

namespace App\Filament\Pages;

use App\Models\Abonos;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PagosDelDia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string $view = 'filament.pages.pagos-del-dia';
    protected static ?string $title = 'Pagos del Día';
    protected static ?string $navigationLabel = 'Pagos del Día';
    protected static bool $shouldRegisterNavigation = false; // No mostrar en el menú principal
    
    public $pagosYapeHoy;
    public $pagosEfectivoHoy;
    public $totalYape;
    public $totalEfectivo;
    public $countYape;
    public $countEfectivo;
    public $totalGeneral;
    public $countGeneral;
    
    public function mount(): void
    {
        $this->loadPagosData();
    }
    
    protected function loadPagosData(): void
    {
        // Obtiene el ID de la ruta seleccionada de la sesión
        $selectedRutaId = Session::get('selected_ruta_id');
        
        // Obtener pagos YAPE del día
        $this->pagosYapeHoy = Abonos::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereHas('conceptosabonos', function ($query) {
                $query->where('tipo_concepto', 'Yape');
            })
            ->with(['credito.cliente', 'conceptosabonos', 'usuario'])
            ->when($selectedRutaId, function ($query) use ($selectedRutaId) {
                $query->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                    $query->where('id_ruta', $selectedRutaId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Obtener pagos EFECTIVO del día
        $this->pagosEfectivoHoy = Abonos::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereHas('conceptosabonos', function ($query) {
                $query->where('tipo_concepto', 'Efectivo');
            })
            ->with(['credito.cliente', 'conceptosabonos', 'usuario'])
            ->when($selectedRutaId, function ($query) use ($selectedRutaId) {
                $query->whereHas('credito.cliente', function ($query) use ($selectedRutaId) {
                    $query->where('id_ruta', $selectedRutaId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Calcular totales
        $this->totalYape = $this->pagosYapeHoy->sum('monto_abono');
        $this->totalEfectivo = $this->pagosEfectivoHoy->sum('monto_abono');
        $this->countYape = $this->pagosYapeHoy->count();
        $this->countEfectivo = $this->pagosEfectivoHoy->count();
        $this->totalGeneral = $this->totalYape + $this->totalEfectivo;
        $this->countGeneral = $this->countYape + $this->countEfectivo;
    }
    
    public function refreshData()
    {
        $this->loadPagosData();
        $this->notify('success', 'Datos actualizados correctamente');
    }
    
    protected function getHeaderActions(): array
    {
        return [
        
             /*   
            \Filament\Actions\ActionGroup::make('back')
                ->label('Volver al Dashboard')
                ->icon('heroicon-o-arrow-left')
                ->url(route('filament.pages.dashboard'))
                ->color('gray'),
                */
        ];
    }
}
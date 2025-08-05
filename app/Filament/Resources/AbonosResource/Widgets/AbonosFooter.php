<?php

namespace App\Filament\Resources\AbonosResource\Widgets;

use App\Models\Abonos;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class AbonosFooter extends Widget

{

    protected static string $view = 'filament.resources.abonos-resource.abonos-footer';
        protected int|string|array $columnSpan = 'full';

    
    // Agregar propiedades públicas para los filtros
    public $fechaDesde;
    public $fechaHasta;
    public $clienteId;
    public $tipoConcepto;

    // Escuchar eventos de la tabla
    protected $listeners = [
    'filter-abonos' => 'applyFilters'
];

public function applyFilters($filters)
{
    $this->clienteId = $filters['clienteId'] ?? null;
    $this->fechaDesde = $filters['fechaDesde'] ?? null;
    $this->fechaHasta = $filters['fechaHasta'] ?? null;
    $this->tipoConcepto = $filters['tipoConcepto'] ?? null;
    $this->emit('$refresh');
}

    protected function getFilteredQuery(): Builder
    {
        $rutaId = Session::get('selected_ruta_id');
        
        return Abonos::query()
            ->when($this->fechaDesde ?? request()->query('fechaDesde'), fn($q) => $q->whereDate('fecha_pago', '>=', $this->fechaDesde ?? request()->query('fechaDesde')))
            ->when($this->fechaHasta ?? request()->query('fechaHasta'), fn($q) => $q->whereDate('fecha_pago', '<=', $this->fechaHasta ?? request()->query('fechaHasta')))
            ->when($this->clienteId ?? request()->query('clienteId'), fn($q) => $q->where('id_cliente', $this->clienteId ?? request()->query('clienteId')))
            ->when($this->tipoConcepto ?? request()->query('tipoConcepto'), fn($q) => $q->whereHas('conceptosabonos', fn($q2) => 
                $q2->where('tipo_concepto', $this->tipoConcepto ?? request()->query('tipoConcepto'))))
            ->when($rutaId, fn($q) => $q->whereHas('cliente', fn($q2) => 
                $q2->where('id_ruta', $rutaId)))
            ->with(['usuario', 'cliente']);
    }

    public function getFooterData()
    {
        return $this->getFilteredQuery()
            ->select([
                'users.id',
                'users.name',
                DB::raw('SUM(abonos.monto_abono) as total_abonos'),
                DB::raw('COUNT(DISTINCT abonos.id_cliente) as clientes_count')
            ])
            ->join('users', 'abonos.id_usuario', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->get();
    }

    

    public function getTotalGeneral()
    {
        return $this->getFooterData()->sum('total_abonos');
    }

    public function getTotalClientes()
    {
        return $this->getFooterData()->sum('clientes_count');
    }

    public function getRutaIdProperty()
    {
        return Session::get('selected_ruta_id');
    }
}
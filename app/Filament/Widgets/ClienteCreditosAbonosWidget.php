<?php

namespace App\Filament\Widgets;

use App\Models\Abonos;
use App\Models\Clientes;
use App\Models\ConceptoAbono;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use App\Models\User;
use App\Models\Ruta;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClienteCreditosAbonosWidget extends Widget
{
    protected static string $view = 'filament.widgets.cliente-creditos-abonos-widget';
    
    public ?int $rutaId = null;
    public array $rutaData = [];
    public array $usuariosData = [];
    public array $creditosData = [];
    public array $abonosData = [];
    public array $clientesData = [];
    public float $totalCreditos = 0;
    public float $totalAbonos = 0;
    public float $saldoPendiente = 0;
    
    // Propiedades para filtro de fechas
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public string $periodoSeleccionado = 'hoy';
    public bool $fechasValidas = true;
    
    protected $listeners = ['ruta-seleccionada' => 'actualizarRuta'];
    
    public function mount(): void
    {
        parent::mount();
        $this->aplicarPeriodo();
    }
    
    public function actualizarRuta(int $rutaId): void
    {
        $this->rutaId = $rutaId;
        $this->aplicarPeriodo();
        $this->cargarDatosRuta();
    }
    
    public function aplicarPeriodo(): void
    {
        $hoy = Carbon::today();

        switch ($this->periodoSeleccionado) {
            case 'hoy':
                $this->fechaDesde = $hoy->toDateString();
                $this->fechaHasta = $hoy->toDateString();
                break;
            case 'ayer':
                $ayer = $hoy->copy()->subDay();
                $this->fechaDesde = $ayer->toDateString();
                $this->fechaHasta = $ayer->toDateString();
                break;
            case 'semana_actual':
                $this->fechaDesde = $hoy->startOfWeek()->toDateString();
                $this->fechaHasta = $hoy->endOfWeek()->toDateString();
                break;
            case 'semana_anterior':
                $start = $hoy->copy()->subWeek()->startOfWeek();
                $end = $hoy->copy()->subWeek()->endOfWeek();
                $this->fechaDesde = $start->toDateString();
                $this->fechaHasta = $end->toDateString();
                break;
            case 'ultimas_2_semanas':
                $this->fechaDesde = $hoy->copy()->subWeeks(2)->startOfWeek()->toDateString();
                $this->fechaHasta = $hoy->endOfWeek()->toDateString();
                break;
            case 'mes_actual':
                $this->fechaDesde = $hoy->startOfMonth()->toDateString();
                $this->fechaHasta = $hoy->endOfMonth()->toDateString();
                break;
            case 'mes_anterior':
                $this->fechaDesde = $hoy->subMonth()->startOfMonth()->toDateString();
                $this->fechaHasta = $hoy->copy()->endOfMonth()->toDateString();
                break;
            case 'personalizado':
            default:
                // No se tocan las fechas
                break;
        }
    }
    
    public function limpiarFiltros(): void
    {
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->periodoSeleccionado = 'hoy';
        $this->aplicarPeriodo();
        $this->fechasValidas = true;
        $this->cargarDatosUsuario();
    }
    
    public function updated($name)
    {
        if (in_array($name, ['fechaDesde', 'fechaHasta', 'periodoSeleccionado'])) {
            if ($name === 'periodoSeleccionado') {
                $this->aplicarPeriodo();
            }
            $this->cargarDatosRuta();
        }
    }
    
    protected function cargarDatosRuta(): void
    {
        if (!$this->rutaId) {
            $this->rutaData = [];
            $this->usuariosData = [];
            $this->creditosData = [];
            $this->abonosData = [];
            $this->clientesData = [];
            $this->totalCreditos = 0;
            $this->totalAbonos = 0;
            $this->saldoPendiente = 0;
            return;
        }
        
        // Obtener datos de la ruta
        $ruta = Ruta::find($this->rutaId);
        if (!$ruta) {
            return;
        }
        
        $this->rutaData = [
            'id' => $ruta->id_ruta,
            'nombre' => $ruta->nombre,
            'descripcion' => $ruta->descripcion,
        ];
        
        // Obtener usuarios de la ruta
        $usuarios = $ruta->usuarios;
        $this->usuariosData = [];
        foreach ($usuarios as $usuario) {
            $this->usuariosData[] = [
                'id' => $usuario->id,
                'nombres' => $usuario->name,
                'email' => $usuario->email,
                'celular' => $usuario->celular,
            ];
        }
        
        // Obtener clientes de la ruta
        $clientes = Clientes::where('id_ruta', $this->rutaId)->get();
        
        
        // Almacenar datos de clientes
        $this->clientesData = [];
        foreach ($clientes as $cliente) {
            $this->clientesData[] = [
                'id' => $cliente->id_cliente,
                'nombre' => $cliente->nombre,
                'documento' => $cliente->documento,
                'direccion' => $cliente->direccion,
                'telefono' => $cliente->telefono,
                'estado' => $cliente->estado,
            ];
        }
        
        $clientesIds = $clientes->pluck('id_cliente')->toArray();
        
        // Obtener créditos con filtro de fechas
        $creditosQuery = Creditos::whereIn('id_cliente', $clientesIds);
        
        if ($this->fechaDesde) {
            $creditosQuery->whereDate('fecha_credito', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $creditosQuery->whereDate('fecha_credito', '<=', $this->fechaHasta);
        }
        
        $creditos = $creditosQuery->get();
        
        $this->creditosData = [];
        $this->totalCreditos = 0;
        
        foreach ($creditos as $credito) {
            $conceptos = ConceptoCredito::where('id_credito', $credito->id_credito)->get();
            $conceptosArray = [];
            
            foreach ($conceptos as $concepto) {
                $conceptosArray[] = [
                    'tipo' => $concepto->tipo_concepto,
                    'monto' => $concepto->monto,
                ];
            }
            
            $this->creditosData[] = [
                'id' => $credito->id_credito,
                'fecha' => Carbon::parse($credito->fecha_credito)->format('d/m/Y'),
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'conceptos' => $conceptosArray,
                'cliente_id' => $credito->id_cliente,
                'cliente_nombre' => $credito->cliente->nombre ?? 'Cliente no encontrado',
            ];
            
            $this->totalCreditos += $credito->valor_credito;
        }
        
        // Obtener abonos con filtro de fechas
        $abonosQuery = Abonos::whereIn('id_cliente', $clientesIds);
        
        if ($this->fechaDesde) {
            $abonosQuery->whereDate('fecha_pago', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $abonosQuery->whereDate('fecha_pago', '<=', $this->fechaHasta);
        }
        
        $abonos = $abonosQuery->get();
        
        $this->abonosData = [];
        $this->totalAbonos = 0;
        
        foreach ($abonos as $abono) {
            $conceptos = ConceptoAbono::where('id_abono', $abono->id_abono)->get();
            $conceptosArray = [];
            
            foreach ($conceptos as $concepto) {
                $conceptosArray[] = [
                    'tipo' => $concepto->tipo_concepto,
                    'monto' => $concepto->monto,
                ];
            }
            
            $this->abonosData[] = [
                'id' => $abono->id_abono,
                'fecha' => Carbon::parse($abono->fecha_pago)->format('d/m/Y'),
                'monto' => $abono->monto_abono,
                'credito_id' => $abono->id_credito,
                'conceptos' => $conceptosArray,
                'cliente_id' => $abono->id_cliente,
                'cliente_nombre' => $abono->cliente->nombre ?? 'Cliente no encontrado',
            ];
            
            $this->totalAbonos += $abono->monto_abono;
        }
        
        $this->saldoPendiente = $this->totalCreditos - $this->totalAbonos;
    }
    
    protected function getViewData(): array
    {
        if (!$this->rutaId) {
            return [
                'rutaSeleccionada' => false,
                'ruta' => null,
                'usuarios' => [],
                'clientes' => [],
                'creditos' => [],
                'abonos' => [],
                'totalCreditos' => 0,
                'totalAbonos' => 0,
                'saldoPendiente' => 0,
            ];
        }
        
        return [
            'rutaSeleccionada' => true,
            'ruta' => $this->rutaData,
            'usuarios' => $this->usuariosData,
            'clientes' => $this->clientesData,
            'creditos' => $this->creditosData,
            'abonos' => $this->abonosData,
            'totalCreditos' => $this->totalCreditos,
            'totalAbonos' => $this->totalAbonos,
            'saldoPendiente' => $this->saldoPendiente,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'fechasValidas' => $this->fechasValidas,
        ];
    }
}
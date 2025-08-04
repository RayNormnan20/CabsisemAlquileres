<?php

namespace App\Filament\Widgets;

use App\Models\Abonos;
use App\Models\Clientes;
use App\Models\ConceptoAbono;
use App\Models\ConceptoCredito;
use App\Models\Creditos;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClienteCreditosAbonosWidget extends Widget
{
    protected static string $view = 'filament.widgets.cliente-creditos-abonos-widget';
    
    public ?int $userId = null;
    public array $userData = [];
    public array $creditosData = [];
    public array $abonosData = [];
    public array $clientesData = []; // Nuevo array para clientes
    public float $totalCreditos = 0;
    public float $totalAbonos = 0;
    public float $saldoPendiente = 0;
    
    protected $listeners = ['usuario-seleccionado' => 'actualizarUsuario'];
    
    public function actualizarUsuario(int $userId): void
    {
        $this->userId = $userId;
        $this->cargarDatosUsuario();
    }
    
    protected function cargarDatosUsuario(): void
    {
        if (!$this->userId) {
            $this->userData = [];
            $this->creditosData = [];
            $this->abonosData = [];
            $this->clientesData = [];
            $this->totalCreditos = 0;
            $this->totalAbonos = 0;
            $this->saldoPendiente = 0;
            return;
        }
        
        // Obtener datos del usuario
        $usuario = User::find($this->userId);
        if (!$usuario) {
            return;
        }
        
        $this->userData = [
            'id' => $usuario->id,
            'nombres' => $usuario->name,
            'email' => $usuario->email,
            'celular' => $usuario->celular,
            'ruta' => $usuario->getRutaPrincipalAttribute() ? $usuario->getRutaPrincipalAttribute()->nombre : 'Sin ruta',
        ];
        
        // Obtener IDs de clientes asociados a las rutas del usuario
        $rutasIds = $usuario->rutas()->pluck('id_ruta')->toArray();
        $clientes = Clientes::whereIn('id_ruta', $rutasIds)->get();
        
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
        
        // Resto del código para créditos y abonos (se mantiene igual)
        $creditos = Creditos::whereIn('id_cliente', $clientesIds)->get();
        
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
                'fecha' => Carbon::parse($credito->fecha_inicio)->format('d/m/Y'),
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'conceptos' => $conceptosArray,
                'cliente_id' => $credito->id_cliente,
                'cliente_nombre' => $credito->cliente->nombre ?? 'Cliente no encontrado',
            ];
            
            $this->totalCreditos += $credito->valor_credito;
        }
        
        $abonos = Abonos::whereIn('id_cliente', $clientesIds)->get();
        
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
                'fecha' => Carbon::parse($abono->fecha)->format('d/m/Y'),
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
        if (!$this->userId) {
            return [
                'usuarioSeleccionado' => false,
                'usuario' => null,
                'clientes' => [],
                'creditos' => [],
                'abonos' => [],
                'totalCreditos' => 0,
                'totalAbonos' => 0,
                'saldoPendiente' => 0,
            ];
        }
        
        return [
            'usuarioSeleccionado' => true,
            'usuario' => $this->userData,
            'clientes' => $this->clientesData,
            'creditos' => $this->creditosData,
            'abonos' => $this->abonosData,
            'totalCreditos' => $this->totalCreditos,
            'totalAbonos' => $this->totalAbonos,
            'saldoPendiente' => $this->saldoPendiente,
        ];
    }
}
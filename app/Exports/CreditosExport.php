<?php

namespace App\Exports;

use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\ConceptoCredito;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditosExport
{
    protected $clienteId;
    protected $fechaDesde;
    protected $fechaHasta;
    protected $clienteData;
    protected $creditosData;
    protected $totalCreditos;

    public function __construct($clienteId = null, $fechaDesde = null, $fechaHasta = null)
    {
        $this->clienteId = $clienteId;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
        
        if ($clienteId !== null || $fechaDesde !== null || $fechaHasta !== null) {
            $this->loadData();
        }
    }

    public function setFilteredData($creditos)
    {
        $this->creditosData = [];
        $this->totalCreditos = 0;
        $this->clienteData = null;

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
                'fecha' => $credito->fecha_credito,
                'fecha_formateada' => Carbon::parse($credito->fecha_credito)->format('d/m/Y'),
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'interes' => $credito->porcentaje_interes,
                'dias_plazo' => $credito->dias_plazo,
                'fecha_vencimiento' => $credito->fecha_vencimiento ? Carbon::parse($credito->fecha_vencimiento)->format('d/m/Y') : '',
                'forma_pago' => $credito->tipoPago ? $credito->tipoPago->nombre : 'Sin especificar',
                'conceptos' => $conceptosArray,
                'total_abonos' => $credito->abonos->sum('monto_abono'),
                'cantidad_abonos' => $credito->abonos->count(),
                'cliente_nombre' => $credito->cliente ? $credito->cliente->nombre_completo : 'Sin cliente',
            ];
            
            $this->totalCreditos += $credito->valor_credito;
        }
    }

    protected function loadData()
    {
        if ($this->clienteId) {
            // Caso 1: Cliente específico seleccionado
            $this->clienteData = Clientes::with('ruta')->find($this->clienteId);
            
            if (!$this->clienteData) {
                throw new \Exception('Cliente no encontrado');
            }

            // Cargar créditos del cliente específico con filtros de fecha
            $creditosQuery = Creditos::where('id_cliente', $this->clienteId)
                ->with(['cliente', 'tipoPago', 'conceptosCredito', 'abonos']);
        } else {
            // Caso 2: Todos los clientes con filtro de fecha
            $this->clienteData = null;

            // Cargar créditos de todos los clientes activos con filtro de fechas
            $creditosQuery = Creditos::with(['cliente', 'tipoPago', 'conceptosCredito', 'abonos'])
                ->join('clientes', 'creditos.id_cliente', '=', 'clientes.id_cliente')
                ->where('clientes.activo', true)
                ->select('creditos.*');
        }
        
        // Aplicar filtros de fecha siempre que estén disponibles
        if ($this->fechaDesde) {
            $creditosQuery->whereDate('creditos.fecha_credito', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $creditosQuery->whereDate('creditos.fecha_credito', '<=', $this->fechaHasta);
        }
        
        $creditos = $creditosQuery->orderBy('fecha_credito', 'desc')->get();
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
                'fecha' => $credito->fecha_credito,
                'fecha_formateada' => Carbon::parse($credito->fecha_credito)->format('d/m/Y'),
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'interes' => $credito->porcentaje_interes,
                'dias_plazo' => $credito->dias_plazo,
                'fecha_vencimiento' => $credito->fecha_vencimiento ? Carbon::parse($credito->fecha_vencimiento)->format('d/m/Y') : '',
                'forma_pago' => $credito->tipoPago ? $credito->tipoPago->nombre : 'Sin especificar',
                'conceptos' => $conceptosArray,
                'total_abonos' => $credito->abonos->sum('monto_abono'),
                'cantidad_abonos' => $credito->abonos->count(),
                'cliente_nombre' => $credito->cliente ? $credito->cliente->nombre_completo : 'Sin cliente',
            ];
            
            $this->totalCreditos += $credito->valor_credito;
        }
    }

    public function exportToPDF()
    {
        try {
            $data = [
                'cliente' => $this->clienteData,
                'creditos' => $this->creditosData,
                'totalCreditos' => $this->totalCreditos,
                'fechaDesde' => $this->fechaDesde ? Carbon::parse($this->fechaDesde)->format('d/m/Y') : null,
                'fechaHasta' => $this->fechaHasta ? Carbon::parse($this->fechaHasta)->format('d/m/Y') : null,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                'totalRegistros' => count($this->creditosData),
            ];

            $pdf = Pdf::loadView('filament.exports.creditos-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'Arial',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                     ]);
            
            // Generar nombre de archivo según el contexto
            if ($this->clienteData) {
                $filename = 'creditos_' . $this->clienteData->nombre_completo . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            } else {
                $fechaStr = '';
                if ($this->fechaDesde && $this->fechaHasta) {
                    $fechaStr = '_' . Carbon::parse($this->fechaDesde)->format('Y-m-d') . '_a_' . Carbon::parse($this->fechaHasta)->format('Y-m-d');
                } elseif ($this->fechaDesde) {
                    $fechaStr = '_desde_' . Carbon::parse($this->fechaDesde)->format('Y-m-d');
                } elseif ($this->fechaHasta) {
                    $fechaStr = '_hasta_' . Carbon::parse($this->fechaHasta)->format('Y-m-d');
                }
                $filename = 'creditos_todos_los_clientes' . $fechaStr . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            }
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            throw new \Exception('Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
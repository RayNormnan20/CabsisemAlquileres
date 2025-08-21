<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Ruta;
use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\ConceptoCredito;
use App\Models\ConceptoAbono;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteCreditosAbonosExport
{
    protected $rutaId;
    protected $isRutaExport;
    protected $fechaDesde;
    protected $fechaHasta;
    protected $rutaData;
    protected $usuariosData;
    protected $creditosData;
    protected $abonosData;
    protected $clientesData;
    protected $totalCreditos;
    protected $totalAbonos;
    protected $saldoPendiente;
    protected $totalEfectivo;
    protected $sobranteCobranza;
    protected $efectivoClientesNoRegistrados;

    public function __construct(int $rutaId, bool $isRutaExport = true, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $this->rutaId = $rutaId;
        $this->isRutaExport = $isRutaExport;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
        $this->cargarDatos();
    }

    protected function cargarDatos(): void
    {
        // Obtener datos de la ruta
        $ruta = Ruta::find($this->rutaId);
        if (!$ruta) {
            throw new \Exception('Ruta no encontrada');
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
        
        $usuariosIds = $usuarios->pluck('id')->toArray();

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

        // Cargar créditos con filtro de fechas
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
                'fecha' => $credito->fecha_credito, // Pasar fecha sin formatear
                'fecha_formateada' => Carbon::parse($credito->fecha_credito)->format('d/m/Y'), // Agregar versión formateada
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'conceptos' => $conceptosArray,
                'cliente_id' => $credito->id_cliente,
                'cliente_nombre' => $credito->cliente->nombre ?? 'Cliente no encontrado',
            ];

            $this->totalCreditos += $credito->valor_credito;
        }

        // Cargar abonos con filtro de fechas
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
        
        // Calcular totales específicos para el PDF
        $this->calcularTotalesEspecificos();
    }
    
    protected function calcularTotalesEspecificos(): void
    {
        // Calcular total de abonos efectivos
        $this->totalEfectivo = 0;
        foreach ($this->abonosData as $abono) {
            foreach ($abono['conceptos'] as $concepto) {
                if (stripos($concepto['tipo'], 'efectivo') !== false) {
                    $this->totalEfectivo += $concepto['monto'];
                }
            }
        }
        
        // Calcular sobrante de cobranza (ABONO SOBRANTE COB sin id_abono, filtrado por usuario)
        $this->sobranteCobranza = 0;
        
        // Primero buscar en los abonos normales
        foreach ($this->abonosData as $abono) {
            foreach ($abono['conceptos'] as $concepto) {
                if (stripos($concepto['tipo'], 'ABONO SOBRANTE COB') !== false) {
                    $this->sobranteCobranza += $concepto['monto'];
                }
            }
        }
        
        // Luego buscar en conceptos de abono sin id_abono (filtrados por ruta específica y fecha)
        $usuariosIds = collect($this->usuariosData)->pluck('id')->toArray();
        $conceptosSinAbono = ConceptoAbono::whereIn('id_usuario', $usuariosIds)
            ->whereNull('id_abono')
            ->where('tipo_concepto', 'ABONO SOBRANTE COB')
            ->where('id_ruta', $this->rutaId) // Validar que pertenezca a la ruta específica
            ->when($this->fechaDesde, function ($query) {
                return $query->whereDate('created_at', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                return $query->whereDate('created_at', '<=', $this->fechaHasta);
            })
            ->get();
            
        foreach ($conceptosSinAbono as $concepto) {
            $this->sobranteCobranza += $concepto->monto;
        }
        
        // EFECTIVO CLIENTES NO REGISTRADOS - Calcular específicamente con tipo 'Efectivo CLi. No Regis.'
        $this->efectivoClientesNoRegistrados = 0;
        
        // Buscar en conceptos de abono sin id_abono con tipo específico (filtrado por fecha)
        $conceptosEfectivoNoRegistrados = ConceptoAbono::whereIn('id_usuario', $usuariosIds)
            ->whereNull('id_abono')
            ->where('tipo_concepto', 'Efectivo CLi. No Regis.')
            ->where('id_ruta', $this->rutaId)
            ->when($this->fechaDesde, function ($query) {
                return $query->whereDate('created_at', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                return $query->whereDate('created_at', '<=', $this->fechaHasta);
            })
            ->get();
            
        foreach ($conceptosEfectivoNoRegistrados as $concepto) {
            $this->efectivoClientesNoRegistrados += $concepto->monto;
        }
    }

    public function exportToPDF()
    {
        try {
            $data = [
                'ruta' => $this->rutaData,
                'usuarios' => $this->usuariosData,
                'clientes' => $this->clientesData,
                'creditos' => $this->creditosData,
                'abonos' => $this->abonosData,
                'totalCreditos' => $this->totalCreditos,
                'totalAbonos' => $this->totalAbonos,
                'saldoPendiente' => $this->saldoPendiente,
                'totalEfectivo' => $this->totalEfectivo,
                'sobranteCobranza' => $this->sobranteCobranza,
                'efectivoClientesNoRegistrados' => $this->efectivoClientesNoRegistrados,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            ];

            $pdf = Pdf::loadView('filament.exports.cliente-creditos-abonos-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'Arial',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                     ]);
            
            $fileName = 'liquidacion-' . str_replace(' ', '-', $this->rutaData['nombre']) . '-' . now()->format('Y-m-d') . '.pdf';
            
            return response()->streamDownload(
                fn () => print($pdf->stream()),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );
            
        } catch (\Exception $e) {
            throw new \Exception('Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
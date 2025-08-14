<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Clientes;
use App\Models\Creditos;
use App\Models\Abonos;
use App\Models\ConceptoCredito;
use App\Models\ConceptoAbono;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteCreditosAbonosExport
{
    protected $userId;
    protected $userData;
    protected $creditosData;
    protected $abonosData;
    protected $clientesData;
    protected $totalCreditos;
    protected $totalAbonos;
    protected $saldoPendiente;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->cargarDatos();
    }

    protected function cargarDatos(): void
    {
        // Obtener datos del usuario
        $usuario = User::find($this->userId);
        if (!$usuario) {
            throw new \Exception('Usuario no encontrado');
        }

        $this->userData = [
            'id' => $usuario->id,
            'nombres' => $usuario->name ?? $usuario->nombres ?? 'Usuario',
            'email' => $usuario->email,
            'celular' => $usuario->celular ?? $usuario->telefono ?? 'No disponible',
            'ruta' => $usuario->getRutaPrincipalAttribute() ? $usuario->getRutaPrincipalAttribute()->nombre : 'Sin ruta',
            'estado' => $usuario->estado ?? 'activo'
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

        // Cargar créditos
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
                'fecha' => $credito->fecha_inicio, // Pasar fecha sin formatear
                'fecha_formateada' => Carbon::parse($credito->fecha_inicio)->format('d/m/Y'), // Agregar versión formateada
                'valor' => $credito->valor_credito,
                'saldo' => $credito->saldo_actual,
                'estado' => $credito->estado,
                'conceptos' => $conceptosArray,
                'cliente_id' => $credito->id_cliente,
                'cliente_nombre' => $credito->cliente->nombre ?? 'Cliente no encontrado',
            ];

            $this->totalCreditos += $credito->valor_credito;
        }

        // Cargar abonos
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

    public function exportToPDF()
    {
        try {
            $data = [
                'usuario' => $this->userData,
                'clientes' => $this->clientesData,
                'creditos' => $this->creditosData,
                'abonos' => $this->abonosData,
                'totalCreditos' => $this->totalCreditos,
                'totalAbonos' => $this->totalAbonos,
                'saldoPendiente' => $this->saldoPendiente,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            ];

            $pdf = Pdf::loadView('filament.exports.cliente-creditos-abonos-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'Arial',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                     ]);
            
            $fileName = 'liquidacion-' . str_replace(' ', '-', $this->userData['nombres']) . '-' . now()->format('Y-m-d') . '.pdf';
            
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
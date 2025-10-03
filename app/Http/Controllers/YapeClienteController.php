<?php

namespace App\Http\Controllers;

use App\Models\YapeCliente;
use App\Models\Abonos;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class YapeClienteController extends Controller
{
    public function getClienteInfo($id)
    {
        $cliente = Clientes::find($id);
        return response()->json($cliente);
    }

    public function getCobradoresPorRuta($rutaId)
    {
        $cobradores = User::whereHas('rutas', function($query) use ($rutaId) {
            $query->where('rutas.id', $rutaId);
        })->get(['id', 'name']);

        return response()->json($cobradores);
    }

    public function generarPDF($id)
    {
        // Obtener el YapeCliente
        $yapeCliente = YapeCliente::with(['cliente', 'user'])->findOrFail($id);

        // Sin límite de descargas: se permite descarga directa

        // Obtener todos los abonos para este YapeCliente con sus conceptos e imágenes
        $abonos = Abonos::where('id_yape_cliente', $id)
            ->with(['cliente', 'conceptosabonos'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener todas las imágenes de comprobantes
        $imagenes = [];
        foreach ($abonos as $abono) {
            foreach ($abono->conceptosabonos as $concepto) {
                if ($concepto->foto_comprobante) {
                    $esDevolucion = $abono->es_devolucion;
                    $imagenes[] = [
                        'url' => storage_path('app/public/' . $concepto->foto_comprobante),
                        'cliente' => $abono->cliente ? $abono->cliente->nombre_completo : 'Sin cliente',
                        'fecha' => $abono->created_at->format('d/m/Y'),
                        'monto' => $abono->monto_abono,
                        'es_devolucion' => $esDevolucion
                    ];
                }
            }
        }

        // Crear el contenido HTML para el PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte de Pagos - ' . $yapeCliente->nombre . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #007bff;
                    margin-bottom: 5px;
                    font-size: 24px;
                }
                .header h2 {
                    color: #666;
                    margin-top: 5px;
                    font-size: 18px;
                }
                .info-section {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .info-item {
                    margin-bottom: 8px;
                }
                .info-label {
                    font-weight: bold;
                    color: #495057;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                th, td {
                    border: 1px solid #dee2e6;
                    padding: 12px;
                    text-align: left;
                }
                th {
                    background-color: #007bff;
                    color: white;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f8f9fa;
                }
                .totales {
                    background-color: #e9ecef;
                    padding: 20px;
                    border-radius: 8px;
                    border-left: 4px solid #28a745;
                }
                .total-item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                    padding: 5px 0;
                }
                .total-label {
                    font-weight: bold;
                    color: #495057;
                }
                .total-value {
                    color: #28a745;
                    font-weight: bold;
                    font-size: 16px;
                }
                .no-data {
                    text-align: center;
                    color: #6c757d;
                    font-style: italic;
                    padding: 20px;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                    border-top: 1px solid #dee2e6;
                    padding-top: 15px;
                }
                .imagenes-section {
                    margin-top: 30px;
                    page-break-inside: avoid;
                }
                .imagenes-titulo {
                    font-size: 18px;
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 20px;
                    text-align: center;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                }
                .imagenes-grid {
                    display: table;
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 10px;
                }
                .imagen-fila {
                    display: table-row;
                }
                .imagen-celda {
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    text-align: center;
                    padding: 10px;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    background-color: #f8f9fa;
                }
                .imagen-comprobante {
                    max-width: 100%;
                    max-height: 200px;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .imagen-info {
                    margin-top: 8px;
                    font-size: 10px;
                    color: #495057;
                }
                .imagen-info strong {
                    color: #007bff;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Pagos</h1>
                <h2>Nombre Yape: ' . $yapeCliente->nombre . '</h2>
                <p>Fecha de generación: ' . now()->format('d/m/Y H:i') . '</p>
            </div>

            <div class="info-section">
                <div class="info-item">
                    <span class="info-label">Cliente Asignado:</span>
                    ' . ($yapeCliente->cliente ? $yapeCliente->cliente->nombre_completo : 'Sin cliente asignado') . '
                </div>
                <div class="info-item">
                    <span class="info-label">Cobrador:</span>
                    ' . ($yapeCliente->user ? $yapeCliente->user->name : 'Sin asignar') . '
                </div>
                <div class="info-item">
                    <span class="info-label">Monto Objetivo:</span>
                    S/ ' . number_format($yapeCliente->monto, 2) . '
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>';

        if ($abonos->count() > 0) {
            foreach ($abonos as $abono) {
                $clienteNombre = $abono->cliente ?
                    (function($nombreCompleto) {
                        $partes = explode(' ', $nombreCompleto);
                        if (count($partes) >= 2) {
                            // Mantener solo el primer nombre y censurar apellidos
                            return $partes[0] . ' ******';
                        }
                        return $nombreCompleto;
                    })($abono->cliente->nombre_completo)
                    : 'Sin cliente';

                // Verificar si es devolución usando el campo es_devolucion
                $esDevolucion = $abono->es_devolucion;
                $colorMonto = $esDevolucion ? 'color: #dc3545;' : 'color: #28a745;';
                $prefijoMonto = $esDevolucion ? '-S/ ' : 'S/ ';
                $monto = '<span style="' . $colorMonto . '">' . $prefijoMonto . number_format($abono->monto_abono, 2) . '</span>';
                $fecha = $abono->created_at->format('d/m/Y H:i');

                $html .= '<tr>
                    <td>' . $clienteNombre . '</td>
                    <td>' . $monto . '</td>
                    <td>' . $fecha . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="3" class="no-data">No hay pagos registrados</td></tr>';
        }

        // Calcular total neto (sumando pagos y restando devoluciones)
        $totalNeto = 0;
        foreach ($abonos as $abono) {
            $esDevolucion = $abono->es_devolucion;
            if ($esDevolucion) {
                $totalNeto -= $abono->monto_abono;
            } else {
                $totalNeto += $abono->monto_abono;
            }
        }

        $faltante = max(0, $yapeCliente->monto - $totalNeto);
        $devolucion = max(0, $totalNeto - $yapeCliente->monto);

        $html .= '
                </tbody>
            </table>

            <div class="totales">
                <div class="total-item">
                    <span class="total-label">Total Neto:</span>
                    <span class="total-value" style="color: ' . ($totalNeto >= 0 ? '#28a745' : '#dc3545') . ';">S/ ' . number_format($totalNeto, 2) . '</span>
                </div>
                <div class="total-item">
                    <span class="total-label">Cantidad de Pagos:</span>
                    <span class="total-value">' . $abonos->count() . ' pagos</span>
                </div>
                <div class="total-item">
                    <span class="total-label">Monto Objetivo:</span>
                    <span class="total-value">S/ ' . number_format($yapeCliente->monto, 2) . '</span>
                </div>';

        if ($faltante > 0) {
            $html .= '<div class="total-item">
                <span class="total-label">Faltante:</span>
                <span class="total-value" style="color: #dc3545;">S/ ' . number_format($faltante, 2) . '</span>
            </div>';
        }

        if ($devolucion > 0) {
            $html .= '<div class="total-item">
                <span class="total-label">Devolución:</span>
                <span class="total-value" style="color: #17a2b8;">S/ ' . number_format($devolucion, 2) . '</span>
            </div>';
        }

        $html .= '
            </div>';

        // Agregar sección de imágenes si existen
        if (count($imagenes) > 0) {
            $html .= '
            <div class="imagenes-section">
                <div class="imagenes-titulo">Comprobantes de Pago</div>
                <div class="imagenes-grid">';

            // Organizar imágenes en filas de 3
            $imagenesChunks = array_chunk($imagenes, 3);

            foreach ($imagenesChunks as $fila) {
                $html .= '<div class="imagen-fila">';

                foreach ($fila as $imagen) {
                    // Verificar si el archivo existe
                    if (file_exists($imagen['url'])) {
                        // Convertir imagen a base64 para embeber en PDF
                        $imageData = base64_encode(file_get_contents($imagen['url']));
                        $imageInfo = getimagesize($imagen['url']);
                        $mimeType = $imageInfo['mime'];

                        $html .= '<div class="imagen-celda">';
                         $html .= '<img src="data:' . $mimeType . ';base64,' . $imageData . '" class="imagen-comprobante" alt="Comprobante">';
                         $html .= '<div class="imagen-info">';
                         $html .= '<strong>Fecha:</strong> ' . $imagen['fecha'] . '<br>';
                         $colorMonto = $imagen['es_devolucion'] ? '#dc3545' : '#28a745';
                         $prefijoMonto = $imagen['es_devolucion'] ? '-S/ ' : 'S/ ';
                         $html .= '<strong>Monto:</strong> <span style="color: ' . $colorMonto . ';">' . $prefijoMonto . number_format($imagen['monto'], 2) . '</span>';
                         $html .= '</div>';
                         $html .= '</div>';
                    }
                }

                // Completar la fila con celdas vacías si es necesario
                $celdasFaltantes = 3 - count($fila);
                for ($i = 0; $i < $celdasFaltantes; $i++) {
                    $html .= '<div class="imagen-celda" style="border: none; background: none;"></div>';
                }

                $html .= '</div>';
            }

            $html .= '
                </div>
            </div>';
        }

        // Nota de seguridad y política dentro del PDF
        $html .= '
            <div class="footer">
                <p>Reporte generado automáticamente por el Sistema de Gestión</p>
                <p style="font-size:12px;color:#555;margin-top:8px;">
                    Política: Documento protegido con contraseña para apertura.
                </p>
                <p>© ' . date('Y') . ' - Todos los derechos reservados</p>
            </div>
        </body>
        </html>';

        // Generar el PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Encriptar PDF:
        // - Contraseña de usuario global: "cabsisem" (siempre disponible para apertura)
        // - Contraseña de propietario: DNI si existe, sino teléfono, y si ninguno existe, "cabsisem"
        $userPassword = 'OFICINA';
        $ownerPassword = 'cabsisem';
        if ($yapeCliente->cliente) {
            $dni = $yapeCliente->cliente->numero_documento ?? null;
            $telefono = $yapeCliente->cliente->telefono ?? null;
            if (!empty($dni)) {
                $ownerPassword = $dni;
            } elseif (!empty($telefono)) {
                $ownerPassword = $telefono;
            }
        }

        try {
            // DomPDF encripta a través del canvas/cpdf
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->get_canvas();
            $cpdf = $canvas->get_cpdf();
            // Permitir imprimir pero no copiar/modificar
            $cpdf->setEncryption($userPassword, $ownerPassword, ['print']);
        } catch (\Throwable $e) {
            // Si falla la encriptación, continuar sin bloquear
        }

        $fileName = 'pagos_' . str_replace(' ', '_', strtolower($yapeCliente->nombre)) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($fileName);
    }
}

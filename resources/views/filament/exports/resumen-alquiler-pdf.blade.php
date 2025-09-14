<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Alquiler - {{ $edificio->nombre }} - Depto. {{ $departamento->numero_departamento }}</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        font-size: 12px;
        line-height: 1.4;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #333;
        padding-bottom: 20px;
    }

    .header h1 {
        color: #333;
        margin: 0;
        font-size: 24px;
    }

    .header h2 {
        color: #666;
        margin: 5px 0;
        font-size: 18px;
    }

    .info-section {
        margin-bottom: 25px;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }

    .info-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: space-between;
    }

    .info-item {
        flex: 0 1 auto;
        white-space: nowrap;
        margin-bottom: 0;
    }

    .info-label {
        font-weight: bold;
        color: #333;
    }

    .info-value {
        color: #555;
    }

    .section-title {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin: 25px 0 15px 0;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #333;
    }

    .table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .status-badge {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-cancelado {
        background-color: #d4edda;
        color: #155724;
    }

    .status-pendiente {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-deuda {
        background-color: #f8d7da;
        color: #721c24;
    }

    .total-section {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    .total-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }

    .total-label {
        font-weight: bold;
    }

    .total-value {
        font-weight: bold;
        color: #333;
    }

    .footer {
        margin-top: 40px;
        text-align: center;
        font-size: 10px;
        color: #666;
        border-top: 1px solid #ddd;
        padding-top: 15px;
    }

    .money {
        font-weight: bold;
        color: #28a745;
    }

    .images-section {
        margin-bottom: 25px;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }

    .images-grid {
        display: table;
        width: 100%;
        border-collapse: separate;
        border-spacing: 10px;
        margin: 10px 0;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .imagen-info {
        margin-top: 8px;
        font-size: 10px;
        color: #495057;
    }

    .imagen-info strong {
        color: #007bff;
    }

    .image-item {
        text-align: center;
        flex: 1;
        max-width: 120px;
    }

    .image-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .image-label {
        font-size: 8px;
        color: #666;
        margin-top: 5px;
    }

    /* Estilos para imágenes en tabla de pagos */
    .payment-images {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        max-width: 80px;
    }

    .payment-image {
        width: 18px;
        height: 18px;
        border-radius: 2px;
        border: 1px solid #ddd;
        object-fit: cover;
    }

    .no-image {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 100px;
        background-color: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 4px;
        font-size: 8px;
        color: #999;
        text-align: center;
    }

    /* Estilos para sección de imágenes al final */
    .payment-images-section {
        margin-top: 30px;
        page-break-inside: avoid;
    }

    .payment-images-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .payment-image-item {
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #f9f9f9;
    }

    .payment-image-item img {
        width: 150px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .payment-image-info {
        margin-top: 8px;
        font-size: 10px;
        color: #495057;
    }

    .payment-image-info strong {
        color: #007bff;
    }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>RESUMEN DE ALQUILER</h1>
        <h2>{{ $edificio->nombre }} - Departamento {{ $departamento->numero_departamento }}</h2>
        <p>Generado el: {{ $fechaGeneracion }}</p>
    </div>

    <!-- Información General -->
    <div class="info-section">
        <h3 class="section-title">Información General</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Edificio:</span>
                <span class="info-value">{{ $edificio->nombre }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Departamento:</span>
                <span class="info-value">{{ $departamento->numero_departamento }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Piso:</span>
                <span class="info-value">{{ $departamento->piso }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Habitaciones:</span>
                <span class="info-value">{{ $departamento->numero_habitaciones }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Baños:</span>
                <span class="info-value">{{ $departamento->numero_banos }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $alquiler->inquilino->nombre_completo ?? 'Sin cliente' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha Inicio:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha Fin:</span>
                <span class="info-value">{{ $alquiler->fecha_fin ? \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') : 'Indefinido' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Monto Mensual:</span>
                <span class="info-value money">S/ {{ number_format($alquiler->precio_mensual, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">{{ ucfirst($alquiler->estado_alquiler) }}</span>
            </div>
        </div>
    </div>

    <!-- Imágenes del Alquiler -->
    @if($alquiler->imagen_1_path || $alquiler->imagen_2_path || $alquiler->imagen_3_path)
    <div class="images-section">
        <h3 class="section-title">Imágenes del Alquiler</h3>
        <div class="images-grid">
            <div class="imagen-fila">
                @if($alquiler->imagen_1_path)
                <div class="imagen-celda">
                    @php
                    $imagePath1 = storage_path('app/public/' . $alquiler->imagen_1_path);
                    $imageData1 = file_exists($imagePath1) ? base64_encode(file_get_contents($imagePath1)) : null;
                    $imageMime1 = $imageData1 ? mime_content_type($imagePath1) : null;
                    @endphp
                    @if($imageData1)
                    <img src="data:{{ $imageMime1 }};base64,{{ $imageData1 }}" class="imagen-comprobante"
                        alt="Imagen 1">
                    @else
                    <div class="no-image">Imagen no disponible</div>
                    @endif
                    <div class="imagen-info"><strong>Imagen 1</strong></div>
                </div>
                @endif
                @if($alquiler->imagen_2_path)
                <div class="imagen-celda">
                    @php
                    $imagePath2 = storage_path('app/public/' . $alquiler->imagen_2_path);
                    $imageData2 = file_exists($imagePath2) ? base64_encode(file_get_contents($imagePath2)) : null;
                    $imageMime2 = $imageData2 ? mime_content_type($imagePath2) : null;
                    @endphp
                    @if($imageData2)
                    <img src="data:{{ $imageMime2 }};base64,{{ $imageData2 }}" class="imagen-comprobante"
                        alt="Imagen 2">
                    @else
                    <div class="no-image">Imagen no disponible</div>
                    @endif
                    <div class="imagen-info"><strong>Imagen 2</strong></div>
                </div>
                @endif
                @if($alquiler->imagen_3_path)
                <div class="imagen-celda">
                    @php
                    $imagePath3 = storage_path('app/public/' . $alquiler->imagen_3_path);
                    $imageData3 = file_exists($imagePath3) ? base64_encode(file_get_contents($imagePath3)) : null;
                    $imageMime3 = $imageData3 ? mime_content_type($imagePath3) : null;
                    @endphp
                    @if($imageData3)
                    <img src="data:{{ $imageMime3 }};base64,{{ $imageData3 }}" class="imagen-comprobante"
                        alt="Imagen 3">
                    @else
                    <div class="no-image">Imagen no disponible</div>
                    @endif
                    <div class="imagen-info"><strong>Imagen 3</strong></div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen de Pagos Mensuales -->
    @if(count($pagosMensuales) > 0)
    <h3 class="section-title">Resumen de Pagos Mensuales</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Mes</th>
                <th>Monto Esperado</th>
                <th>Monto Pagado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagosMensuales as $pago)
            <tr>
                <td>{{ $pago['mes'] }}</td>
                <td class="money">S/ {{ number_format($pago['total'], 2) }}</td>
                <td class="money">S/ {{ number_format($pago['pagado'], 2) }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $pago['estado'])) }}">
                        {{ $pago['estado'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Detalle de Pagos -->
    @if(count($detallesPagos) > 0)
    <h3 class="section-title">Detalle de Pagos Realizados</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Mes/Año</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Cobrador</th>
                <th>Observaciones</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detallesPagos as $detalle)
            <tr>
                <td>{{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}</td>
                <td>{{ str_pad($detalle['mes_correspondiente'], 2, '0', STR_PAD_LEFT) }}/{{ $detalle['ano_correspondiente'] }}
                </td>
                <td class="money">S/ {{ number_format($detalle['monto_pagado'], 2) }}</td>
                <td>{{ ucfirst($detalle['metodo_pago']) }}</td>
                <td>{{ $detalle['cobrador_nombre'] }}</td>
                <td>{{ $detalle['observaciones'] ?? '-' }}</td>
                <td>
                    @if(isset($detalle['recibo_path']) && $detalle['recibo_path'])
                    <div class="payment-images">
                        @php
                        $reciboPathSmall = storage_path('app/public/' . $detalle['recibo_path']);
                        $reciboDataSmall = file_exists($reciboPathSmall) ?
                        base64_encode(file_get_contents($reciboPathSmall)) : null;
                        $reciboMimeSmall = $reciboDataSmall ? mime_content_type($reciboPathSmall) : null;
                        @endphp
                        @if($reciboDataSmall)
                        <img src="data:{{ $reciboMimeSmall }};base64,{{ $reciboDataSmall }}" alt="Comprobante"
                            class="payment-image">
                        @else
                        <span style="font-size: 8px; color: #999;">No disponible</span>
                        @endif
                    </div>
                    @else
                    <span style="font-size: 10px; color: #999;">Sin foto</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Totales -->
    <div class="total-section">
        <h3 class="section-title">Resumen de Totales</h3>
        <div class="total-item">
            <span class="total-label">Total de Abonos Realizados:</span>
            <span class="total-value money">S/ {{ number_format($totalAbonos, 2) }}</span>
        </div>
        <div class="total-item">
            <span class="total-label">Número de Pagos:</span>
            <span class="total-value">{{ count($detallesPagos) }}</span>
        </div>
    </div>

    <!-- Sección de Imágenes de Comprobantes -->
    @php
    $imagenesComprobantes = collect($detallesPagos)->filter(function($detalle) {
    return isset($detalle['recibo_path']) && !empty($detalle['recibo_path']);
    })->take(8);
    @endphp

    @if($imagenesComprobantes->count() > 0)
    <div class="payment-images-section">
        <h3 class="section-title">Comprobantes de Pago</h3>
        <div class="payment-images-grid">
            @foreach($imagenesComprobantes as $index => $detalle)
            <div class="payment-image-item">
                @php
                $reciboPath = storage_path('app/public/' . $detalle['recibo_path']);
                $reciboData = file_exists($reciboPath) ? base64_encode(file_get_contents($reciboPath)) : null;
                $reciboMime = $reciboData ? mime_content_type($reciboPath) : null;
                @endphp
                @if($reciboData)
                <img src="data:{{ $reciboMime }};base64,{{ $reciboData }}" alt="Comprobante {{ $index + 1 }}">
                @else
                <div class="no-image">Comprobante no disponible</div>
                @endif
                <div class="payment-image-info">
                    <strong>Comprobante {{ $index + 1 }}</strong><br>
                    Fecha: {{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}<br>
                    Monto: S/ {{ number_format($detalle['monto_pagado'], 2) }}<br>
                    Método: {{ ucfirst($detalle['metodo_pago']) }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Alquileres</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>

</html>

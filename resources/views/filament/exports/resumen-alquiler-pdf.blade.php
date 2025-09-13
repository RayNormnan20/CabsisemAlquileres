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
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            margin-bottom: 8px;
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
            <div>
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
            </div>
            <div>
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
    </div>

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
            </tr>
        </thead>
        <tbody>
            @foreach($detallesPagos as $detalle)
            <tr>
                <td>{{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}</td>
                <td>{{ str_pad($detalle['mes_correspondiente'], 2, '0', STR_PAD_LEFT) }}/{{ $detalle['ano_correspondiente'] }}</td>
                <td class="money">S/ {{ number_format($detalle['monto_pagado'], 2) }}</td>
                <td>{{ ucfirst($detalle['metodo_pago']) }}</td>
                <td>{{ $detalle['cobrador_nombre'] }}</td>
                <td>{{ $detalle['observaciones'] ?? '-' }}</td>
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

    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Alquileres</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>
</html>
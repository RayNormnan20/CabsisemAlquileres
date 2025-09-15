<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos de Alquiler</title>
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
        
        .filters-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        
        .filters-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
        }
        
        .filter-item {
            flex: 0 1 auto;
            white-space: nowrap;
        }
        
        .filter-label {
            font-weight: bold;
            color: #333;
        }
        
        .filter-value {
            color: #555;
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
            font-size: 11px;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .money {
            text-align: right;
            font-weight: bold;
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
            padding-top: 20px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE PAGOS DE ALQUILER</h1>
        <h2>Sistema de Gestión de Alquileres</h2>
        <p>Generado el: {{ $fechaGeneracion }}</p>
    </div>
    
    <!-- Filtros Aplicados -->
    <div class="filters-info">
        <h3 style="margin-top: 0;">Filtros Aplicados</h3>
        <div class="filters-grid">
            @if($fechaDesde || $fechaHasta)
            <div class="filter-item">
                <span class="filter-label">Período:</span>
                <span class="filter-value">
                    @if($fechaDesde && $fechaHasta)
                        {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                    @elseif($fechaDesde)
                        Desde {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                    @elseif($fechaHasta)
                        Hasta {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                    @endif
                </span>
            </div>
            @endif
            
            @if($edificioSeleccionado)
            <div class="filter-item">
                <span class="filter-label">Edificio:</span>
                <span class="filter-value">{{ $pagos->first()->alquiler->departamento->edificio->nombre ?? 'N/A' }}</span>
            </div>
            @endif
            
            @if($departamentoSeleccionado)
            <div class="filter-item">
                <span class="filter-label">Departamento:</span>
                <span class="filter-value">{{ $pagos->first()->alquiler->departamento->numero_departamento ?? 'N/A' }}</span>
            </div>
            @endif
            
            <div class="filter-item">
                <span class="filter-label">Total de registros:</span>
                <span class="filter-value">{{ $pagos->count() }}</span>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Pagos -->
    <table class="table">
        <thead>
            <tr>
                <th>Fecha Pago</th>
                <th>Edificio</th>
                <th>Depto.</th>
                <th>Inquilino</th>
                <th>Mes/Año</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Referencia</th>
                <th>Registrado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                <td>{{ $pago->alquiler->departamento->edificio->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->alquiler->departamento->numero_departamento ?? 'N/A' }}</td>
                <td>{{ $pago->alquiler->inquilino->nombre_completo ?? 'N/A' }}</td>
                <td>{{ str_pad($pago->mes_correspondiente, 2, '0', STR_PAD_LEFT) }}/{{ $pago->ano_correspondiente }}</td>
                <td class="money">S/ {{ number_format($pago->monto_pagado, 2) }}</td>
                <td>{{ ucfirst($pago->metodo_pago) }}</td>
                <td>{{ $pago->referencia_pago ?? '-' }}</td>
                <td>{{ $pago->usuarioRegistro->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Resumen Totales -->
    <div class="total-section">
        <h3 style="margin-top: 0;">Resumen de Totales</h3>
        <div class="total-item">
            <span class="total-label">Total de Pagos:</span>
            <span class="total-value">{{ $pagos->count() }} registros</span>
        </div>
        <div class="total-item">
            <span class="total-label">Monto Total:</span>
            <span class="total-value">S/ {{ number_format($pagos->sum('monto_pagado'), 2) }}</span>
        </div>
        <div class="total-item">
            <span class="total-label">Promedio por Pago:</span>
            <span class="total-value">S/ {{ number_format($pagos->avg('monto_pagado'), 2) }}</span>
        </div>
    </div>
    
    <!-- Resumen por Método de Pago -->
    @php
        $pagosPorMetodo = $pagos->groupBy('metodo_pago');
    @endphp
    
    @if($pagosPorMetodo->count() > 1)
    <div class="total-section">
        <h3 style="margin-top: 0;">Resumen por Método de Pago</h3>
        @foreach($pagosPorMetodo as $metodo => $pagosPorMetodoItem)
        <div class="total-item">
            <span class="total-label">{{ ucfirst($metodo) }}:</span>
            <span class="total-value">{{ $pagosPorMetodoItem->count() }} pagos - S/ {{ number_format($pagosPorMetodoItem->sum('monto_pagado'), 2) }}</span>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Alquileres</p>
        <p>Fecha y hora de generación: {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>
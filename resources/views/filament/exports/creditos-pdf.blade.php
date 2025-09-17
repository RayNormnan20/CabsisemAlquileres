<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Créditos{{ $cliente ? ' - ' . $cliente->nombre_completo : ' - Todos los Clientes' }}</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10px;
        margin: 0;
        padding: 15px;
        color: #000;
        line-height: 1.2;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .header h1 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        text-decoration: underline;
    }

    .info-section {
        margin-bottom: 15px;
        border: 1px solid #000;
        padding: 10px;
        background-color: #f9f9f9;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }

    .info-label {
        font-weight: bold;
        width: 30%;
    }

    .info-value {
        width: 70%;
    }

    .creditos-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .creditos-table th,
    .creditos-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
        font-size: 8px;
    }

    .creditos-table th {
        background-color: #e0e0e0;
        font-weight: bold;
        font-size: 9px;
    }

    .creditos-table .text-left {
        text-align: left;
    }

    .creditos-table .text-right {
        text-align: right;
    }

    .conceptos-list {
        font-size: 7px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .conceptos-list li {
        margin-bottom: 1px;
    }

    .totals-section {
        margin-top: 15px;
        border: 2px solid #000;
        padding: 10px;
        background-color: #f0f0f0;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 8px;
        border-top: 1px solid #000;
        padding-top: 10px;
    }

    .estado-activo {
        color: #d32f2f;
        font-weight: bold;
    }

    .estado-pagado {
        color: #388e3c;
        font-weight: bold;
    }

    .page-break {
        page-break-after: always;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>LISTADO DE CRÉDITOS</h1>
        @if($cliente)
        <p style="margin: 5px 0; font-size: 12px;">{{ $cliente->nombre_completo }}</p>
        @if($cliente->ruta)
        <p style="margin: 5px 0;">Ruta: {{ $cliente->ruta->nombre }}</p>
        @endif
        @else
        <p style="margin: 5px 0; font-size: 12px;">TODOS LOS CLIENTES</p>
        @endif
    </div>

    <div class="info-section">
        @if($cliente)
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value">{{ $cliente->nombre_completo }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DNI:</span>
            <span class="info-value">{{ $cliente->dni ?? 'No registrado' }}</span>
        </div>
        @else
        <div class="info-row">
            <span class="info-label">Reporte:</span>
            <span class="info-value">Créditos de todos los clientes activos</span>
        </div>
        @endif
        @if($fechaDesde && $fechaHasta)
        <div class="info-row">
            <span class="info-label">Período:</span>
            <span class="info-value">{{ $fechaDesde }} - {{ $fechaHasta }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total de Créditos:</span>
            <span class="info-value">{{ $totalRegistros }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de Generación:</span>
            <span class="info-value">{{ $fechaGeneracion }}</span>
        </div>
    </div>

    @if(count($creditos) > 0)
    <table class="creditos-table">
        <thead>
            <tr>
                <th style="width: 6%;">ID</th>
                @if(!$cliente)
                <th style="width: 15%;">Cliente</th>
                @endif
                <th style="width: {{ !$cliente ? '8%' : '10%' }};">Fecha</th>
                <th style="width: {{ !$cliente ? '10%' : '12%' }};">Valor</th>
                <th style="width: {{ !$cliente ? '10%' : '12%' }};">Saldo</th>
                <th style="width: {{ !$cliente ? '6%' : '8%' }};">Interés %</th>
                <th style="width: {{ !$cliente ? '8%' : '10%' }};">Días Plazo</th>
                <th style="width: {{ !$cliente ? '8%' : '10%' }};">Vencimiento</th>
                <th style="width: {{ !$cliente ? '10%' : '12%' }};">Forma Pago</th>
                <th style="width: {{ !$cliente ? '8%' : '10%' }};">Abonos</th>
                <th style="width: {{ !$cliente ? '6%' : '8%' }};">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creditos as $credito)
            <tr>
                <td>{{ $credito['id'] }}</td>
                @if(!$cliente)
                <td class="text-left">{{ $credito['cliente_nombre'] }}</td>
                @endif
                <td>{{ $credito['fecha_formateada'] }}</td>
                <td class="text-right">S/ {{ number_format($credito['valor'], 2) }}</td>
                <td class="text-right">S/ {{ number_format($credito['saldo'], 2) }}</td>
                <td>{{ $credito['interes'] }}%</td>
                <td>{{ $credito['dias_plazo'] }}</td>
                <td>{{ $credito['fecha_vencimiento'] }}</td>
                <td class="text-left">{{ $credito['forma_pago'] }}</td>
                <td class="text-right">
                    S/ {{ number_format($credito['total_abonos'], 2) }}
                    <br><small>({{ $credito['cantidad_abonos'] }} abonos)</small>
                </td>
                <td class="{{ $credito['saldo'] > 0 ? 'estado-activo' : 'estado-pagado' }}">
                    {{ $credito['saldo'] > 0 ? 'ACTIVO' : 'PAGADO' }}
                </td>
            </tr>
            @if(count($credito['conceptos']) > 0)
            <tr>
                <td colspan="{{ !$cliente ? '11' : '10' }}" class="text-left" style="background-color: #f5f5f5;">
                    <strong>Conceptos:</strong>
                    <ul class="conceptos-list">
                        @foreach($credito['conceptos'] as $concepto)
                        <li>{{ $concepto['tipo'] }}: S/ {{ number_format($concepto['monto'], 2) }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span>TOTAL CRÉDITOS OTORGADOS:</span>
            <span>S/ {{ number_format($totalCreditos, 2) }}</span>
        </div>
        <div class="totals-row">
            <span>TOTAL SALDO PENDIENTE:</span>
            <span>S/ {{ number_format(collect($creditos)->sum('saldo'), 2) }}</span>
        </div>
        <div class="totals-row">
            <span>TOTAL ABONOS RECIBIDOS:</span>
            <span>S/ {{ number_format(collect($creditos)->sum('total_abonos'), 2) }}</span>
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 50px; border: 1px solid #ccc; background-color: #f9f9f9;">
        <h3>No se encontraron créditos</h3>
        <p>No hay créditos registrados para el período seleccionado.</p>
    </div>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente el {{ $fechaGeneracion }}</p>
        <p>Sistema de Gestión de Créditos - CABSISEM</p>
    </div>
</body>

</html>

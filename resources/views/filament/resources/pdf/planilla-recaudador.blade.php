<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Planilla Recaudador - {{ $rutaNombre }}</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .title {
        font-size: 16px;
        font-weight: bold;
    }

    .subtitle {
        font-size: 14px;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .text-right {
        text-align: right;
    }

    .footer {
        margin-top: 20px;
        font-size: 10px;
        text-align: right;
    }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Planilla Recaudador</div>
        <div class="subtitle">Ruta: {{ $rutaNombre }} | Filtro: {{ $estadoCredito }} | Orden: {{ $orden }}</div>
        <div>Fecha: {{ $fecha }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>U. Abono</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Crédito</th>
                <th>Abonos</th>
                <th>Saldo</th>
                <th>Cuota</th>
                <th>Atraso (días)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record->fecha_credito->format('d/m/Y') }}</td>
                <td>{{ $record->ultima_fecha_pago ? $record->ultima_fecha_pago->format('d/m/Y') : '-' }}</td>
                <td>{{ $record->cliente_completo }}</td>
                <td>{{ $record->telefono }}</td>
                <td class="text-right">S/ {{ number_format($record->valor_credito, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->total_abonos, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->saldo_actual, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->valor_cuota, 2) }}</td>
                <td>{{ $record->dias_atraso}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>

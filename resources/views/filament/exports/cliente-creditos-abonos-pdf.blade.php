<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuadre Diario de Cobrador - Ruta {{ $ruta['nombre'] }}</title>
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

    .info-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 10px;
    }

    .section {
        margin-bottom: 15px;
    }

    .section-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 5px;
        text-decoration: underline;
    }

    .form-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .form-table td {
        border: 1px solid #000;
        padding: 3px 5px;
        font-size: 9px;
        vertical-align: top;
    }

    .form-table .label {
        background-color: #f0f0f0;
        font-weight: bold;
        width: 60%;
    }

    .form-table .value {
        width: 40%;
        text-align: right;
    }

    .input-box {
        border: 1px solid #000;
        height: 15px;
        width: 80px;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        background-color: white;
        font-size: 9px;
        line-height: 15px;
    }

    .small-box {
        width: 40px;
    }

    .medium-box {
        width: 60px;
    }

    .large-box {
        width: 100px;
    }

    .yape-section {
        background-color: #ffe6e6;
        padding: 5px;
        margin: 5px 0;
        border: 1px solid #000;
    }

    .yape-label {
        color: #ff0000;
        font-weight: bold;
        font-size: 8px;
    }

    .total-box {
        border: 2px solid #ff0000;
        padding: 3px;
        text-align: center;
        font-weight: bold;
        background-color: #ffe6e6;
    }

    .calculation {
        font-style: italic;
        color: #666;
        font-size: 8px;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 5px;
    }

    .prestamos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
    }

    .prestamos-table td {
        border: 1px solid #000;
        padding: 2px;
        text-align: center;
    }

    .prestamos-table .cliente-col {
        text-align: left;
        width: 40%;
    }

    .otros-section {
        border: 1px solid #000;
        padding: 5px;
        margin-top: 10px;
    }

    .firma-section {
        margin-top: 20px;
        text-align: center;
    }

    .firma-box {
        border: 1px solid #000;
        height: 40px;
        width: 200px;
        margin: 0 auto;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>CUADRE DIARIO DE COBRADOR</h1>
    </div>

    <div class="info-line">
        <span>Ruta: {{ $ruta['nombre'] }}</span>
        <span>Fecha: {{ date('d/m/Y') }}</span>
    </div>

    @if(count($usuarios) > 0)
    <div class="info-line">
        <span>Usuarios:
            @foreach($usuarios as $index => $usuario)
            {{ $usuario['nombres'] }}@if($index < count($usuarios) - 1), @endif @endforeach </span>
    </div>
    @endif



    <!-- SECCIÓN DE RENDICIÓN DE COBRADOR -->
    <div class="section">
        <!-- <div class="section-title">RENDICIÓN DE COBRADOR</div> -->

        <table class="form-table">
            <tr>
                <td class="label">Ingresos al Cobrador</td>
                <td class="value"></td>
            </tr>
            <tr>
                <td class="label">Efectivo</td>
                <td class="value">
                    <span class="input-box">{{ number_format($totalEfectivo ?? 0, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">EFECTIVO CLIENTES NO REGISTRADOS</td>
                <td class="value">
                    <span class="input-box">{{ number_format($efectivoClientesNoRegistrados ?? 0, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Sobrante de cobranza</td>
                <td class="value">
                    <span class="input-box">{{ number_format($sobranteCobranza ?? 0, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label" style="font-weight: bold;">TOTAL EFECTIVO</td>
                <td class="value">
                    <span
                        class="input-box total-box">{{ number_format(($totalEfectivo ?? 0) + ($efectivoClientesNoRegistrados ?? 0) + ($sobranteCobranza ?? 0), 0) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- SECCIÓN DE NUEVOS PRÉSTAMOS -->
    <div class="section">
        <div class="section-title">NUEVOS PRÉSTAMOS</div>
        <table class="prestamos-table">
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td>DESCRIPCIÓN</td>
                <td class="cliente-col">NOMBRE</td>
                <td>MONTO</td>
                <td>EFECTIVO</td>
                <td>YAPE</td>
                <td>TOTAL</td>
            </tr>
            @if(count($nuevosPrestamos ?? []) > 0)
            @foreach($nuevosPrestamos as $prestamo)
            <tr>
                <td>{{ $prestamo['descripcion'] }}</td>
                <td class="cliente-col">{{ $prestamo['cliente_nombre'] }}</td>
                <td style="color: red; font-weight: bold;">-S/.
                    {{ number_format($prestamo['valor_credito'] ?? ($prestamo['monto_efectivo'] + $prestamo['monto_yape']), 0) }}
                </td>
                <td>{{ $prestamo['monto_efectivo'] > 0 ? 'S/. ' . number_format($prestamo['monto_efectivo'], 0) : '-' }}
                </td>
                <td>{{ $prestamo['monto_yape'] > 0 ? 'S/. ' . number_format($prestamo['monto_yape'], 0) : '-' }}</td>
                <td>S/. {{ number_format($prestamo['monto_efectivo'] + $prestamo['monto_yape'], 0) }}</td>
            </tr>
            @endforeach
            @endif

            @if(isset($renovaciones) && count($renovaciones) > 0)
                @foreach($renovaciones as $index => $renovacion)
                    <tr>
                        <td>RENOVACIÓN {{ $index + 1 }}</td>
                        <td class="cliente-col">{{ $renovacion['cliente_nombre'] }}</td>
                        <td style="color: red; font-weight: bold;">-S/. {{ number_format($renovacion['valor_credito'], 0) }}</td>
                        <td>{{ $renovacion['monto_efectivo'] > 0 ? 'S/. ' . number_format($renovacion['monto_efectivo'], 0) : '-' }}</td>
                        <td>{{ $renovacion['monto_yape'] > 0 ? 'S/. ' . number_format($renovacion['monto_yape'], 0) : '-' }}</td>
                        <td>S/. {{ number_format($renovacion['monto_efectivo'] + $renovacion['monto_yape'], 0) }}</td>
                    </tr>
                @endforeach
            @endif

            @if(count($nuevosPrestamos ?? []) == 0 && (!isset($renovaciones) || count($renovaciones) == 0))
            <tr>
                <td colspan="6" style="text-align: center; font-style: italic; color: #666;">No hay
                    nuevos préstamos ni renovaciones en este período</td>
            </tr>
            @endif
            <tr style="background-color: #ffe6e6; font-weight: bold;">
                <td>TOTAL PRÉSTAMOS Y RENOVACIONES</td>
                <td class="cliente-col">{{ count($nuevosPrestamos ?? []) + count($renovaciones ?? []) }}</td>
                <td style="color: red; font-weight: bold;">-S/.
                    {{ number_format((collect($nuevosPrestamos ?? [])->sum('valor_credito') ?: (collect($nuevosPrestamos ?? [])->sum('monto_efectivo') + collect($nuevosPrestamos ?? [])->sum('monto_yape'))) + collect($renovaciones ?? [])->sum('valor_credito'), 0) }}
                </td>
                <td>S/.
                    {{ number_format(collect($nuevosPrestamos ?? [])->sum('monto_efectivo') + collect($renovaciones ?? [])->sum('monto_efectivo'), 0) }}
                </td>
                <td>S/.
                    {{ number_format(collect($nuevosPrestamos ?? [])->sum('monto_yape') + collect($renovaciones ?? [])->sum('monto_yape'), 0) }}
                </td>
                <td>S/.
                    {{ number_format(collect($nuevosPrestamos ?? [])->sum('monto_efectivo') + collect($nuevosPrestamos ?? [])->sum('monto_yape') + collect($renovaciones ?? [])->sum('monto_efectivo') + collect($renovaciones ?? [])->sum('monto_yape'), 0) }}
                </td>
            </tr>
        </table>
    </div>



    <!-- FIRMA -->
    <div class="firma-section">
        <div>Firma: _________________ Dato: _________________</div>
        <div class="firma-box" style="margin-top: 10px;"></div>
    </div>

    <!-- RESUMEN DE DATOS -->
    <div style="margin-top: 20px; font-size: 8px; border-top: 1px solid #000; padding-top: 10px;">
        <strong>RESUMEN DE DATOS:</strong><br>
        Total Clientes: {{ count($clientes) }} |
        Total Créditos: S/. {{ number_format($totalCreditos, 2) }} |
        Total Abonos: S/. {{ number_format($totalAbonos, 2) }} |
        Saldo: S/. {{ number_format($saldoPendiente, 2) }}<br>
        Nuevos Préstamos: {{ count($nuevosPrestamos ?? []) }} |
        Valor Nuevos Préstamos: S/. {{ number_format(collect($nuevosPrestamos ?? [])->sum('valor_credito'), 2) }}<br>
        Generado: {{ date('d/m/Y H:i:s') }} | Ruta: {{ $ruta['nombre'] }}
    </div>
</body>

</html>
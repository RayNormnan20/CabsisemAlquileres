<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuadre Diario de Cobrador - {{ $usuario['nombres'] }}</title>
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
        <span>Nombre: {{ $usuario['nombres'] }}</span>
        <span>Fecha: {{ date('d/m/Y') }}</span>
    </div>

    <!-- SECCIÓN DE INGRESOS -->
    <div class="section">
        <div class="section-title">INGRESOS</div>

        <table class="form-table">
            <tr>
                <td class="label">1.- Ingresos realizados al cobrador.</td>
                <td class="value">
                    <span class="input-box">{{ number_format($totalAbonos, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Efectivo realizado al cobrador</td>
                <td class="value">
                    <span class="input-box">{{ number_format($totalAbonos * 0.8, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Efectivo no registrado</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">Sobrante de ruta</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label" style="font-weight: bold;">Total efectivo</td>
                <td class="value">
                    <span class="input-box total-box">{{ number_format($totalAbonos * 0.8, 0) }}</span>
                </td>
            </tr>
        </table>

        <table class="form-table">
            <tr>
                <td class="label">Yapes realizados al cobrador</td>
                <td class="value">
                    <span class="input-box">{{ number_format($totalAbonos * 0.2, 0) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Plin realizado al cobrador</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">2.- Ingresos realizados a Cristian.</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">Efectivo realizado a Cristian</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">Yapes realizados a Cristian</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">Plin realizado a Cristian</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">3.- Abono a préstamos.</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr>
                <td class="label">Cobrador</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
        </table>

        <div class="section-title">4.- Ingresos realizados a nuevos préstamos de clientes.</div>

        <table class="prestamos-table">
            <tr>
                <td style="font-weight: bold;">Cobrador</td>
                <td style="font-weight: bold;">Registrados</td>
                <td style="font-weight: bold;">Devolución</td>
                <td style="font-weight: bold;">Total YAPE</td>
            </tr>
            @php
            $creditosHoy = collect($creditos)->filter(function($credito) {
            return \Carbon\Carbon::parse($credito['fecha'])->isToday();
            });
            $totalCreditosHoy = $creditosHoy->sum('valor');
            @endphp
            @foreach(collect($creditosHoy)->take(5) as $credito)
            <tr>
                <td class="cliente-col">{{ $credito['cliente_nombre'] ?? 'Cliente' }}</td>
                <td><span class="yape-label">YAPE</span> <span
                        class="input-box small-box">{{ number_format($credito['valor'], 0) }}</span></td>
                <td><span class="input-box small-box">0</span></td>
                <td><span class="input-box small-box">{{ number_format($credito['valor'], 0) }}</span></td>
            </tr>
            @endforeach
            @for($i = collect($creditosHoy)->count(); $i < 5; $i++) <tr>
                <td class="cliente-col">Cliente</td>
                <td><span class="yape-label">YAPE</span> <span class="input-box small-box">0</span></td>
                <td><span class="input-box small-box">0</span></td>
                <td><span class="input-box small-box">0</span></td>
                </tr>
                @endfor
                <tr>
                    <td style="font-weight: bold;">Cobrador No registrados</td>
                    <td><span class="input-box small-box">0</span></td>
                    <td><span class="input-box small-box">0</span></td>
                    <td><span class="input-box small-box">0</span></td>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <td style="font-weight: bold;">* TOTAL INGRESOS AL COBRADOR</td>
                    <td colspan="3">
                        <span
                            class="input-box total-box large-box">{{ number_format($totalAbonos + $totalCreditosHoy, 0) }}</span>
                    </td>
                </tr>
        </table>
    </div>

    <!-- SECCIÓN DE EGRESOS -->
    <div class="section">
        <div class="section-title">EGRESOS</div>

        <table class="form-table">
            <tr>
                <td class="label">1.- Egresos para nuevos préstamos (Del mismo cob PRESTAMO)</td>
                <td class="value">
                    <span class="input-box">{{ number_format($totalCreditos, 0) }}</span>
                </td>
            </tr>
        </table>

        <table class="prestamos-table">
            <tr>
                <td style="font-weight: bold;">Telf.</td>
                <td style="font-weight: bold;">Cliente</td>
                <td style="font-weight: bold;">Entregado</td>
                <td style="font-weight: bold;">Devolución</td>
            </tr>
            @foreach(collect($creditos)->take(5) as $credito)
            <tr>
                <td>{{ $credito['cliente_telefono'] ?? 'N/A' }}</td>
                <td class="cliente-col">{{ $credito['cliente_nombre'] ?? 'Cliente' }}</td>
                <td><span class="input-box small-box">{{ number_format($credito['valor'], 0) }}</span></td>
                <td><span class="input-box small-box">0</span></td>
            </tr>
            @endforeach
            @for($i = count($creditos); $i < 5; $i++) <tr>
                <td>Telf.</td>
                <td class="cliente-col">Cliente</td>
                <td><span class="input-box small-box">0</span></td>
                <td><span class="input-box small-box">0</span></td>
                </tr>
                @endfor
                <tr style="background-color: #f0f0f0;">
                    <td colspan="2" style="font-weight: bold;">TOTAL</td>
                    <td><span class="input-box total-box small-box">{{ number_format($totalCreditos, 0) }}</span></td>
                    <td><span class="input-box small-box">0</span></td>
                </tr>
        </table>

        <table class="form-table">
            <tr>
                <td class="label">Transferencia a cobrador</td>
                <td class="value">
                    <span class="input-box">0</span>
                </td>
            </tr>
            <tr style="background-color: #f0f0f0;">
                <td class="label" style="font-weight: bold;">* TOTAL EGRESOS DEL COBRADOR</td>
                <td class="value">
                    <span class="input-box total-box">{{ number_format($totalCreditos, 0) }}</span>
                </td>
            </tr>
            <tr style="background-color: #ffe6e6;">
                <td class="label" style="font-weight: bold;">SALDO A RENDIR (Efectivo - Egresos)</td>
                <td class="value">
                    <span class="input-box total-box">{{ number_format($saldoPendiente, 0) }}</span>
                    <span class="calculation">(= {{ number_format($totalAbonos, 0) }} -
                        {{ number_format($totalCreditos, 0) }})</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- OTROS INGRESOS Y EGRESOS -->
    <div class="otros-section">
        <div class="section-title">Otros ingresos y egresos (Campo obligatorio)</div>
        <div class="grid-2">
            <div>
                <strong>Otros ingresos:</strong><br>
                Otros ingresos: <span class="input-box medium-box">0</span><br>
                Otros egresos: <span class="input-box medium-box">0</span>
            </div>
            <div style="text-align: right;">
                <strong>*Efectivo a entregar*</strong><br>
                <span class="input-box total-box large-box">{{ number_format($saldoPendiente, 0) }}</span>
            </div>
        </div>
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
        Generado: {{ date('d/m/Y H:i:s') }} | Usuario: {{ $usuario['nombres'] }}
    </div>
</body>

</html>
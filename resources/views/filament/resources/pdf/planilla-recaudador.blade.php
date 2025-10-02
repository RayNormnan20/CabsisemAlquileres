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

    @if($creditosRegulares->isNotEmpty())
    <div style="background-color: #f59e0b; color: white; padding: 8px; font-weight: bold; margin-bottom: 10px;">
        PLANILLA RECAUDADOR
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
            @foreach($creditosRegulares as $index => $record)
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
            <tr style="background-color: #f59e0b; color: white; font-weight: bold;">
                <td colspan="5">TOTALES</td>
                <td class="text-right">S/ {{ number_format($creditosRegulares->sum('valor_credito'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosRegulares->sum('total_abonos'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosRegulares->sum('saldo_actual'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosRegulares->sum('valor_cuota'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    @if($creditosAdicionales->isNotEmpty())
    <div style="background-color: #10b981; color: white; padding: 8px; font-weight: bold; margin: 20px 0 10px 0;">
        ADICIONALES
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
            @foreach($creditosAdicionales as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record->fecha_credito->format('d/m/Y') }}</td>
                <td>{{ $record->ultima_fecha_pago ? $record->ultima_fecha_pago->format('d/m/Y') : 'Nunca' }}</td>
                <td>{{ $record->cliente_completo }}</td>
                <td>{{ $record->telefono }}</td>
                <td class="text-right">S/ {{ number_format($record->valor_credito, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->total_abonos, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->saldo_actual, 2) }}</td>
                <td class="text-right">S/ {{ number_format($record->cuota_real, 2) }}</td>
                <td class="text-center">{{ $record->dias_atraso > 0 ? $record->dias_atraso : 0 }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #10b981; color: white; font-weight: bold;">
                <td colspan="5">TOTALES</td>
                <td class="text-right">S/ {{ number_format($creditosAdicionales->sum('valor_credito'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosAdicionales->sum('total_abonos'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosAdicionales->sum('saldo_actual'), 2) }}</td>
                <td class="text-right">S/ {{ number_format($creditosAdicionales->sum('cuota_real'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

     <!-- TOTAL GENERAL -->
      <div style="margin-top: 30px; padding: 15px; background-color: #f0f9ff; border: 2px solid #10b981; border-radius: 8px;">
          <h3 style="text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">TOTAL GENERAL</h3>
          <table style="width: 100%; border: none;">
              <tr>
                  <td style="text-align: center; border: none; padding: 10px;">
                      <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Crédito Total</div>
                      <div style="font-size: 14px; font-weight: bold; color: #1f2937;">S/ {{ number_format($totalGeneral['credito'], 2) }}</div>
                  </td>
                  <td style="text-align: center; border: none; padding: 10px;">
                      <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Abonos Total</div>
                      <div style="font-size: 14px; font-weight: bold; color: #10b981;">S/ {{ number_format($totalGeneral['abonos'], 2) }}</div>
                  </td>
                  <td style="text-align: center; border: none; padding: 10px;">
                      <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Saldo Total</div>
                      <div style="font-size: 14px; font-weight: bold; color: #dc2626;">S/ {{ number_format($totalGeneral['saldo'], 2) }}</div>
                  </td>
              </tr>
          </table>
      </div>

     <div class="footer">
         Generado el {{ now()->format('d/m/Y H:i') }}
     </div>
</body>

</html>

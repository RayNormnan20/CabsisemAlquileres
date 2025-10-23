<!-- Contenido específico de Cantidad Abonado -->
<div class="mb-4">

</div>

<!-- Tabla de Resultados -->
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-600">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-route mr-1"></i>Ruta
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-calculator mr-1"></i>Cant.
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-dollar-sign mr-1"></i>Monto
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @php
            // Filtrar solo abonos de tipo Efectivo y Yape
            $abonosEfectivoYape = collect($datosAbonos ?? [])->filter(function($abono) {
                return collect($abono->conceptosabonos)->contains(function($concepto) {
                    return in_array($concepto->tipo_concepto, ['Efectivo', 'Yape']);
                });
            });
            
            // Agrupar abonos filtrados por ruta
            $abonosPorRuta = $abonosEfectivoYape->groupBy(function($abono) {
                return $abono->credito->cliente->ruta->nombre ?? 'Sin Ruta';
            })->map(function($abonos, $ruta) {
                return [
                    'ruta' => $ruta,
                    'cantidad' => $abonos->count(),
                    'monto' => $abonos->sum('monto_abono')
                ];
            });
            @endphp

            @forelse($abonosPorRuta as $dato)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $dato['ruta'] }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center font-semibold">
                    {{ number_format($dato['cantidad']) }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-green-600 text-center">
                    S/ {{ number_format($dato['monto'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-3 block"></i>
                    <p class="text-gray-500">No hay datos disponibles para el rango de fechas seleccionado</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($abonosPorRuta->count() > 0)
        <tfoot class="bg-gray-800">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">TOTAL</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                    {{ number_format($abonosPorRuta->sum('cantidad')) }}
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                    S/ {{ number_format($abonosPorRuta->sum('monto'), 2) }}
                </th>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
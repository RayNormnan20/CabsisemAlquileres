<!-- Contenido específico de Préstamos Entregados -->
<div class="mb-4">

</div>

<!-- Tabla de Resultados -->
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-600">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-route mr-2"></i>Ruta
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-hashtag mr-2"></i>Cantidad
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-dollar-sign mr-2"></i>Monto Total
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @php
            // Usar datos reales de créditos del controlador y agrupar por ruta
            $datosPrestamos = $datosCreditos ?? [];

            // Agrupar préstamos por ruta
            $prestamosPorRuta = collect($datosPrestamos)->groupBy(function($credito) {
            return $credito->cliente->ruta->nombre ?? 'Sin Ruta';
            })->map(function($creditos, $ruta) {
            return [
            'ruta' => $ruta,
            'cantidad' => $creditos->count(),
            'monto_total' => $creditos->sum('valor_credito')
            ];
            });
            @endphp

            @forelse($prestamosPorRuta as $datos)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $datos['ruta'] }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    {{ $datos['cantidad'] }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 text-center">
                    S/ {{ number_format($datos['monto_total'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center">
                    <i class="fas fa-handshake text-gray-400 text-4xl mb-3 block"></i>
                    <p class="text-gray-500">No hay préstamos entregados en el rango de fechas seleccionado</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($prestamosPorRuta->count() > 0)
        <tfoot class="bg-gray-800">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">TOTAL</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-white">
                    {{ $prestamosPorRuta->sum('cantidad') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-white">
                    S/ {{ number_format($prestamosPorRuta->sum('monto_total'), 2) }}
                </th>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

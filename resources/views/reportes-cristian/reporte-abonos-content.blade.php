<!-- Contenido específico de Reporte de Abonos -->
<div class="mb-4">

</div>

@php
// ORDEN ESPECÍFICO de conceptos según requerimiento del usuario
$ordenEspecifico = [
    'Yape',
    'Efectivo',
    'Abono completar p.',
    'Abono sin firma Chis',
    'OTROS EGRESOS',
    'OTROS INGRESOS',
    'ABONO SOBRANTE COB',
    'ABONO FALTANTE COB',
    'SUELDO COBRADOR',
    'ABONO DE DESCUENTO',
    'ENTREGA CAJA COBRADOR',
    'Efectivo CLi. No Regis.',
    'Abono de Bajo Cuenta',
    'Abono de Renovación',
    'Cancelado'
];

// Obtener todos los conceptos únicos de la base de datos
$conceptosEnBD = \App\Models\ConceptoAbono::distinct()
    ->pluck('tipo_concepto')
    ->filter() // Eliminar valores nulos
    ->toArray();

// Combinar orden específico con conceptos adicionales de la BD
/*
$todosLosConceptos = [];
foreach ($ordenEspecifico as $concepto) {
    if (in_array($concepto, $conceptosEnBD)) {
        $todosLosConceptos[] = $concepto;
    }
}
*/
// Siempre incluir todos los del orden específico, aunque no existan en BD
$todosLosConceptos = $ordenEspecifico;
// Agregar conceptos de la BD que no están en el orden específico
foreach ($conceptosEnBD as $concepto) {
    if (!in_array($concepto, $todosLosConceptos)) {
        $todosLosConceptos[] = $concepto;
    }
}

// Inicializar arrays para montos y cantidades
$conceptosPorRuta = [];
$cantidadPorConcepto = [];
foreach ($todosLosConceptos as $concepto) {
    $conceptosPorRuta[$concepto] = 0;
    $cantidadPorConcepto[$concepto] = 0;
}

// Sumar montos y contar cantidad por concepto
foreach ($datosAbonos as $abono) {
    foreach ($abono->conceptosabonos as $conceptoAbono) {
        $conceptoOriginal = $conceptoAbono->tipo_concepto;
        $monto = $conceptoAbono->monto;
        
        // Sumar monto y contar cantidad
        if (isset($conceptosPorRuta[$conceptoOriginal])) {
            $conceptosPorRuta[$conceptoOriginal] += $monto;
            $cantidadPorConcepto[$conceptoOriginal]++;
        }
    }
}

// Sumar conceptos sin id_abono (movimientos independientes)
if (isset($conceptosSinAbono)) {
    foreach ($conceptosSinAbono as $conceptoAbono) {
        $conceptoOriginal = $conceptoAbono->tipo_concepto;
        $monto = $conceptoAbono->monto;
        
        // Sumar monto y contar cantidad
        if (isset($conceptosPorRuta[$conceptoOriginal])) {
            $conceptosPorRuta[$conceptoOriginal] += $monto;
            $cantidadPorConcepto[$conceptoOriginal]++;
        }
    }
}

// Los totales ya están calculados en $conceptosPorRuta
@endphp

<!-- Tabla de Resultados -->
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-600">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-tags mr-1"></i>Concepto
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-hashtag mr-1"></i>Cantidad
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-calculator mr-1"></i>Total
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($todosLosConceptos as $concepto)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $concepto }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium {{ ($cantidadPorConcepto[$concepto] ?? 0) > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                    {{ $cantidadPorConcepto[$concepto] ?? 0 }}
                </td>
                <td
                    class="px-2 py-2 whitespace-nowrap text-sm text-center font-semibold {{ ($conceptosPorRuta[$concepto] ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    {{ number_format($conceptosPorRuta[$concepto] ?? 0, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                    No hay datos disponibles para el período seleccionado
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900">
                    <i class="fas fa-calculator mr-1"></i>TOTAL GENERAL
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-center text-blue-600">
                    {{ array_sum($cantidadPorConcepto) }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-center text-red-600">
                    {{ number_format(array_sum($conceptosPorRuta), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
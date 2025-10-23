<!-- Contenido específico de Reporte de Abonos -->
<div class="mb-4">

</div>

@php
// Lista completa de conceptos en el orden específico solicitado
$conceptosPredeterminados = [
'Yape',
'Efectivo',
'Cancelado',
'Abono para completar',
'Abono son firma achistaan',
'Otros egresos',
'Otros ingresos',
'Abono sobrante',
'Abono faltante',
'Sueldo cobrador',
'Efectivo cliente no registrado',
'Abono de descuento'
];

// Mapeo para normalizar conceptos (convertir variaciones a formato estándar)
$mapeoConceptos = [
'YAPE' => 'Yape',
'Yape' => 'Yape',
'EFECTIVO' => 'Efectivo',
'Efectivo' => 'Efectivo',
'CANCELADO' => 'Cancelado',
'Cancelado' => 'Cancelado',
'ABONO PARA COMPRA' => 'Abono para completar',
'Abono para completar' => 'Abono para completar',
'ABONO SON FIRMA ACHISTAAN' => 'Abono son firma achistaan',
'Abono son firma achistaan' => 'Abono son firma achistaan',
'OTROS EGRESOS' => 'Otros egresos',
'Otros egresos' => 'Otros egresos',
'OTROS INGRESOS' => 'Otros ingresos',
'Otros ingresos' => 'Otros ingresos',
'ABONO SOBRANTE' => 'Abono sobrante',
'Abono sobrante' => 'Abono sobrante',
'ABONO FALTANTE' => 'Abono faltante',
'Abono faltante' => 'Abono faltante',
'SUELDO COBRADOR' => 'Sueldo cobrador',
'Sueldo cobrador' => 'Sueldo cobrador',
'EFECTIVO CLIENTE NO REGISTRADO' => 'Efectivo cliente no registrado',
'Efectivo cliente no registrado' => 'Efectivo cliente no registrado',
'ABONO DE DESCUENTO' => 'Abono de descuento',
'Abono de descuento' => 'Abono de descuento'
];

// Usar solo los conceptos predeterminados en el orden específico
$todosLosConceptos = $conceptosPredeterminados;

// Inicializar todos los conceptos con 0
$conceptosPorRuta = [];
foreach ($todosLosConceptos as $concepto) {
$conceptosPorRuta[$concepto] = 0;
}

// Agrupar abonos por concepto y sumar montos (normalizando nombres)
foreach ($datosAbonos as $abono) {
foreach ($abono->conceptosabonos as $conceptoAbono) {
$conceptoOriginal = $conceptoAbono->tipo_concepto;
$monto = $conceptoAbono->monto;

// Normalizar el concepto usando el mapeo
$conceptoNormalizado = isset($mapeoConceptos[$conceptoOriginal])
? $mapeoConceptos[$conceptoOriginal]
: $conceptoOriginal;

// Solo sumar si el concepto normalizado está en nuestra lista predeterminada
if (in_array($conceptoNormalizado, $todosLosConceptos)) {
$conceptosPorRuta[$conceptoNormalizado] += $monto;
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
                <td
                    class="px-2 py-2 whitespace-nowrap text-sm text-center font-semibold {{ ($conceptosPorRuta[$concepto] ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    {{ number_format($conceptosPorRuta[$concepto] ?? 0, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-6 py-4 text-center text-gray-500">
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
                <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-center text-red-600">
                    {{ number_format(array_sum($conceptosPorRuta), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
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

// Helpers de formato y timestamp seguros
$fmtDate = function($dt){ if ($dt instanceof \Carbon\Carbon) return $dt->format('Y-m-d H:i'); if (is_string($dt)) { $ts = strtotime($dt); return $ts ? date('Y-m-d H:i', $ts) : null; } return null; };
$tsFrom = function($dt){ if ($dt instanceof \Carbon\Carbon) return $dt->timestamp; if (is_string($dt)) { $ts = strtotime($dt); return $ts ?: null; } return null; };

// Preparar registros detallados para el modal (cliente, usuario, ruta, fecha, referencia)
$records = [];
foreach ($datosAbonos as $abono) {
    foreach ($abono->conceptosabonos as $conceptoAbono) {
        $records[] = [
            'concepto' => $conceptoAbono->tipo_concepto,
            'monto' => (float) $conceptoAbono->monto,
            'fecha' => $fmtDate($abono->fecha_pago ?? $abono->created_at),
            'cliente' => optional($abono->cliente)->nombreCompleto ?? null,
            'usuario' => optional($abono->usuario)->name ?? null,
            'ruta' => optional($abono->ruta)->nombre ?? optional($abono->cliente->ruta)->nombre ?? ($abono->id_ruta ?? null),
            'ruta_id' => $abono->id_ruta ?? optional($abono->ruta)->id_ruta ?? optional($abono->cliente->ruta)->id_ruta ?? null,
            'fecha_ts' => $tsFrom($abono->fecha_pago ?? $abono->created_at),
            // 'comprobante' => $conceptoAbono->foto_comprobante, // oculto segun requerimiento
            'origen' => optional($abono->concepto)->nombre ?? optional($abono->concepto)->tipo ?? 'Abono',
        ];
    }
}
if (isset($conceptosSinAbono)) {
    foreach ($conceptosSinAbono as $conceptoAbono) {
        $records[] = [
            'concepto' => $conceptoAbono->tipo_concepto,
            'monto' => (float) $conceptoAbono->monto,
            'fecha' => $fmtDate($conceptoAbono->created_at),
            'cliente' => null,
            'usuario' => optional($conceptoAbono->usuario)->name ?? null,
            'ruta' => optional($conceptoAbono->ruta)->nombre ?? ($conceptoAbono->id_ruta ?? null),
            'ruta_id' => $conceptoAbono->id_ruta ?? optional($conceptoAbono->ruta)->id ?? null,
            'fecha_ts' => $tsFrom($conceptoAbono->created_at),
            // 'comprobante' => $conceptoAbono->foto_comprobante,
            'origen' => 'Movimiento',
        ];
    }
}

// Los totales ya están calculados en $conceptosPorRuta
@endphp

<!-- Tabla de Resultados (interactiva) -->
<div x-data="{ open:false, selectedConcept:null, selectedRutaId: null, selectedRutaName: @entangle('rutaSeleccionada'), dateStart: @js($this->fechaDesde ? \Carbon\Carbon::parse($this->fechaDesde)->format('Y-m-d') : null), dateEnd: @js($this->fechaHasta ? \Carbon\Carbon::parse($this->fechaHasta)->format('Y-m-d') : null), records: @js($records), filtered(){ return this.records.filter(r => { if (this.selectedConcept && r.concepto !== this.selectedConcept) return false; const tsMs = ((r.fecha_ts ?? 0) * 1000); const startMs = this.dateStart ? Date.parse(this.dateStart) : null; const endMs = this.dateEnd ? Date.parse(this.dateEnd) + 86399999 : null; if (startMs && tsMs < startMs) return false; if (endMs && tsMs > endMs) return false; const currentRuta = (this.selectedRutaName && String(this.selectedRutaName) !== 'todas') ? String(this.selectedRutaName) : null; if (currentRuta) { const matchId = r.ruta_id != null && String(r.ruta_id) === currentRuta; const matchName = r.ruta && String(r.ruta) === currentRuta; if (!(matchId || matchName)) return false; } return true; }).sort((a,b) => ((a.fecha_ts ?? 0) > (b.fecha_ts ?? 0) ? -1 : 1)); } }" class="bg-white rounded-lg shadow w-full">
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
                <td class="px-3 py-2 whitespace-normal break-words text-sm font-medium text-gray-900">
                    <button type="button" class="text-blue-600 hover:text-blue-800 cursor-pointer relative group focus:outline-none"
                        @click="selectedConcept='{{ addslashes($concepto) }}'; open=true" aria-label="Ver detalles de {{ addslashes($concepto) }}">
                        <span>{{ $concepto }}</span>
                        <span class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded shadow opacity-0 group-hover:opacity-100 group-focus:opacity-100">Ver detalles</span>
                    </button>
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

    <!-- Modal de detalle por concepto -->
    <div x-show="open" x-transition @keydown.window.escape="open=false" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;">
        <!-- Fondo -->
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="open=false"></div>
        <!-- Contenido -->
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl mx-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-base font-semibold text-gray-900">
                    Detalle: <span x-text="selectedConcept"></span>
                </h3>
                <button class="p-2 rounded-full hover:bg-gray-100 text-gray-700 focus:outline-none z-10" @click="open=false" aria-label="Cerrar modal">
                    <span class="block leading-none text-2xl">&times;</span>
                </button>
            </div>
            <div class="max-h:[60vh] sm:max-h-[70vh] overflow-y-auto p-4">
                <template x-if="filtered().length === 0">
                    <p class="text-sm text-gray-500">No hay registros para este concepto en el período.</p>
                </template>
                <div class="space-y-3" x-data="{ toMoney(v){ try { return 'S/ ' + Number(v||0).toFixed(2); } catch(e){ return 'S/ 0.00'; } } }">
                    <template x-for="(item, idx) in filtered()" :key="idx">
                        <div class="rounded-lg p-3 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-gray-900" x-text="toMoney(item.monto)"></div>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700" x-show="item.origen" x-text="item.origen"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 text-gray-700" x-show="item.ruta">Ruta: <span class="ml-1" x-text="item.ruta"></span></span>
                                    </div>
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <i class="far fa-clock"></i>
                                        <span x-text="item.fecha"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                                <template x-if="item.cliente">
                                    <div>Cliente: <span class="font-medium" x-text="item.cliente"></span></div>
                                </template>
                                <template x-if="item.usuario">
                                    <div>Usuario: <span class="font-medium" x-text="item.usuario"></span></div>
                                </template>
                            </div>
                            <div class="mt-1 text-sm text-gray-500" x-show="item.referencia">Ref: <span x-text="item.referencia"></span></div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="px-4 py-3 border-t flex justify-end">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm" @click="open=false">Cerrar</button>
            </div>
        </div>
    </div>
</div>
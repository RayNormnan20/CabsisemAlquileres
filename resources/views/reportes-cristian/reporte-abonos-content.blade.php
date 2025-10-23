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
@endphp

<!-- Tabla de Resultados (interactiva) -->
<div x-data="{ open:false, selectedConcept:null, selectedRutaId: null, selectedRutaName: @entangle('rutaSeleccionada'), periodo: @entangle('periodoSeleccionado'), dateStartRaw: @entangle('fechaDesde'), dateEndRaw: @entangle('fechaHasta'), records: $wire.records, fmtDate(ms){ return ms ? new Date(ms).toLocaleString('es-PE') : null; }, parseDateMs(v){ if(!v) return null; const sRaw = String(v).trim(); const s = sRaw.toLowerCase().replace(',', '').replace(/\./g,''); const hasTime = /\d{2}:\d{2}/.test(sRaw) || /t\d{2}:\d{2}/.test(sRaw); if (hasTime) { const d = Date.parse(sRaw); if(!isNaN(d)) return d; } let m = s.match(/^(\d{2})[\\\/\-](\d{2})[\\\/\-](\d{4})$/); if(m){ const dd = parseInt(m[1],10), mm = parseInt(m[2],10)-1, yy = parseInt(m[3],10); return new Date(yy, mm, dd).getTime(); } m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/); if(m){ const yy = parseInt(m[1],10), mm = parseInt(m[2],10)-1, dd = parseInt(m[3],10); return new Date(yy, mm, dd).getTime(); } const d2 = Date.parse(sRaw); return isNaN(d2) ? null : d2; }, toMoney(v){ const n = Number(v||0); return new Intl.NumberFormat('es-PE', { style:'currency', currency:'PEN' }).format(n); }, rangeFromPeriodo(p){ const today = new Date(); let start = new Date(today.getFullYear(), today.getMonth(), today.getDate()); let end = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23,59,59,999); const id = String(p||'').toLowerCase(); if(id==='hoy'){ /* start/end ya seteados */ } else if(id==='ayer'){ start.setDate(start.getDate()-1); end.setDate(end.getDate()-1); } else if(id==='esta_semana'){ const dow = start.getDay(); const diff = (dow===0?6:(dow-1)); start.setDate(start.getDate()-diff); end = new Date(start); end.setDate(start.getDate()+6); end.setHours(23,59,59,999); } else if(id==='semana_pasada'){ const dow = start.getDay(); const diff = (dow===0?6:(dow-1)); const startOfThisWeek = new Date(start); startOfThisWeek.setDate(startOfThisWeek.getDate()-diff); start = new Date(startOfThisWeek); start.setDate(startOfThisWeek.getDate()-7); end = new Date(startOfThisWeek); end.setDate(end.getDate()-1); end.setHours(23,59,59,999); } else if(id==='este_mes'){ start.setDate(1); end = new Date(start.getFullYear(), start.getMonth()+1, 0, 23,59,59,999); } else if(id==='mes_pasado'){ start = new Date(start.getFullYear(), start.getMonth()-1, 1); end = new Date(start.getFullYear(), start.getMonth(), 0, 23,59,59,999); } return { startMs: start.getTime(), endMs: end.getTime() }; }, appliedRange(){ const sRaw = this.dateStartRaw; const eRaw = this.dateEndRaw; let startMs = this.parseDateMs(sRaw); let endBase = this.parseDateMs(eRaw); if(!startMs || !endBase){ const r = this.rangeFromPeriodo(this.periodo); if(!startMs) startMs = r.startMs; if(!endBase) endBase = r.endMs; } const endMs = endBase ? (endBase + 86399999) : null; const startFmt = this.fmtDate(startMs); const endFmt = this.fmtDate(endMs); return { startMs, endMs, startFmt, endFmt }; }, filtered(){ const selected = (this.selectedConcept||'').trim().toLowerCase(); const norm = (s)=>String(s||'').trim().toLowerCase(); const selRuta = (this.selectedRutaName && this.selectedRutaName !== 'todas') ? norm(this.selectedRutaName) : null; const { startMs, endMs } = this.appliedRange(); const res = (this.records||[]).filter(it=>{ const tSec = Number(it.fecha_ts||0); const t = tSec ? (tSec*1000) : null; const inRange = t ? (t>=startMs && t<=endMs) : true; const matchesConcept = selected ? norm(it.concepto)===selected : true; const matchesRuta = selRuta ? (norm(it.ruta)===selRuta) : true; return inRange && matchesConcept && matchesRuta; }); console.log('[Modal Abonos] Estado filtros', { selectedConcept:this.selectedConcept, periodo:this.periodo, dateStartRaw:this.dateStartRaw, dateEndRaw:this.dateEndRaw, rutaSeleccionada:this.selectedRutaName, startMs, endMs, startFmt:this.fmtDate(startMs), endFmt:this.fmtDate(endMs), total:(this.records||[]).length }); console.log('[Modal Abonos] Resultado filtro', { count: res.length, sample: res.slice(0,3).map(x=>({ ts:x.fecha_ts, fecha:x.fecha, concepto:x.concepto, ruta:x.ruta, ruta_id:x.ruta_id }))}); return res; } }" x-effect="records = $wire.records">
  <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 px-3 py-2 border-b">
    <div class="text-sm text-gray-700">
      <span class="font-medium">Rango:</span>
      <span x-text="(appliedRange().startFmt ?? '—') + ' — ' + (appliedRange().endFmt ?? '—')"></span>
    </div>
    <div class="text-sm text-gray-700">
      <span class="font-medium">Ruta:</span>
      <span x-text="(selectedRutaName && selectedRutaName !== 'todas') ? selectedRutaName : 'todas'"></span>
    </div>
    <div class="text-sm text-gray-700">
      <span class="font-medium">Registros:</span>
      <span x-text="filtered().length"></span>
    </div>
  </div>
  <!-- resto de la tabla permanece igual -->
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
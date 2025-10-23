<!-- Contenido específico de Préstamos Entregados -->
<div class="mb-4">

</div>

<!-- Tabla de Resultados -->
<div x-data="{ open:false, selectedRutaName:null, periodo: @entangle('periodoSeleccionado'), dateStartRaw: @entangle('fechaDesde'), dateEndRaw: @entangle('fechaHasta'), creditos: @entangle('creditosRecords'), fmtDate(ms){ return ms ? new Date(ms).toLocaleDateString('es-PE') : null; }, parseDateMs(v){ if(!v) return null; const sRaw = String(v).trim(); const s = sRaw.toLowerCase().replace(',', '').replace(/\./g,''); const hasTime = /\d{2}:\d{2}/.test(sRaw) || /t\d{2}:\d{2}/.test(sRaw); if (hasTime) { const d = Date.parse(sRaw); if(!isNaN(d)) return d; } let m = s.match(/^(\d{2})[\\\/-](\d{2})[\\\/-](\d{4})$/); if(m){ const dd = parseInt(m[1],10), mm = parseInt(m[2],10)-1, yy = parseInt(m[3],10); return new Date(yy, mm, dd).getTime(); } m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/); if(m){ const yy = parseInt(m[1],10), mm = parseInt(m[2],10)-1, dd = parseInt(m[3],10); return new Date(yy, mm, dd).getTime(); } const d2 = Date.parse(sRaw); return isNaN(d2) ? null : d2; }, rangeFromPeriodo(p){ const today = new Date(); let start = new Date(today.getFullYear(), today.getMonth(), today.getDate()); let end = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23,59,59,999); const id = String(p||'').toLowerCase(); if(id==='hoy'){ } else if(id==='ayer'){ start.setDate(start.getDate()-1); end.setDate(end.getDate()-1); } else if(id==='esta_semana'){ const dow = start.getDay(); const diff = (dow===0?6:(dow-1)); start.setDate(start.getDate()-diff); end = new Date(start); end.setDate(start.getDate()+6); end.setHours(23,59,59,999); } else if(id==='semana_pasada'){ const dow = start.getDay(); const diff = (dow===0?6:(dow-1)); const startOfThisWeek = new Date(start); startOfThisWeek.setDate(startOfThisWeek.getDate()-diff); start = new Date(startOfThisWeek); start.setDate(startOfThisWeek.getDate()-7); end = new Date(startOfThisWeek); end.setDate(end.getDate()-1); end.setHours(23,59,59,999); } else if(id==='este_mes'){ start.setDate(1); end = new Date(start.getFullYear(), start.getMonth()+1, 0, 23,59,59,999); } else if(id==='mes_pasado'){ start = new Date(start.getFullYear(), start.getMonth()-1, 1); end = new Date(start.getFullYear(), start.getMonth(), 0, 23,59,59,999); } return { startMs: start.getTime(), endMs: end.getTime() }; }, appliedRange(){ const sRaw = this.dateStartRaw; const eRaw = this.dateEndRaw; let startMs = this.parseDateMs(sRaw); let endBase = this.parseDateMs(eRaw); if(!startMs || !endBase){ const r = this.rangeFromPeriodo(this.periodo); if(!startMs) startMs = r.startMs; if(!endBase) endBase = r.endMs; } const endMs = endBase ? (endBase + 86399999) : null; const startFmt = this.fmtDate(startMs); const endFmt = this.fmtDate(endMs); return { startMs, endMs, startFmt, endFmt }; }, filtered(){ const norm = (s)=>String(s||'').trim().toLowerCase(); const selRuta = (this.selectedRutaName && this.selectedRutaName !== 'todas') ? norm(this.selectedRutaName) : null; const { startMs, endMs } = this.appliedRange(); const res = (this.creditos||[]).filter(it=>{ const t = Number(it.fecha_ts||0)*1000 || null; const inRange = t ? (t>=startMs && t<=endMs) : true; const matchesRuta = selRuta ? (norm(it.ruta)===selRuta) : true; return inRange && matchesRuta; }); return res; }, toMoney(v){ const n = Number(v||0); return new Intl.NumberFormat('es-PE', { style:'currency', currency:'PEN' }).format(n); } }" class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-600">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-route mr-1"></i>Ruta
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-hashtag mr-1"></i>Cant.
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">
                    <i class="fas fa-dollar-sign mr-1"></i>Monto Total
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
                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    <button type="button" class="text-blue-600 hover:text-blue-800 cursor-pointer" @click="selectedRutaName='{{ addslashes($datos['ruta']) }}'; open=true" aria-label="Ver préstamos de {{ addslashes($datos['ruta']) }}">
                        {{ $datos['ruta'] }}
                    </button>
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center font-semibold">
                    {{ $datos['cantidad'] }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-blue-600 text-center">
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
                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">TOTAL</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                    {{ $prestamosPorRuta->sum('cantidad') }}
                </th>
                <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                    S/ {{ number_format($prestamosPorRuta->sum('monto_total'), 2) }}
                </th>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Modal de detalle por ruta (Préstamos) -->
    <div x-show="open" x-transition @keydown.window.escape="open=false" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <!-- Fondo -->
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="open=false"></div>
        <!-- Contenido -->
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl mx-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-base font-semibold text-gray-900">
                    Préstamos entregados — <span x-text="selectedRutaName"></span>
                </h3>
                <button class="p-2 rounded-full hover:bg-gray-100 text-gray-700 focus:outline-none z-10" @click="open=false" aria-label="Cerrar modal">
                    <span class="block leading-none text-2xl">&times;</span>
                </button>
            </div>
            <div class="max-h-[70vh] sm:max-h-[75vh] overflow-y-auto overscroll-contain p-4">
                <template x-if="filtered().length === 0">
                    <p class="text-sm text-gray-500">No hay préstamos en el período para esta ruta.</p>
                </template>
                <div class="space-y-3">
                    <template x-for="(item, idx) in filtered()" :key="idx">
                        <div class="rounded-lg p-3 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-gray-900" x-text="toMoney(item.monto)"></div>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 text-gray-700" x-show="item.ruta">Ruta: <span class="ml-1" x-text="item.ruta"></span></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700" x-show="item.tipo_pago" x-text="item.tipo_pago"></span>
                                    </div>
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <i class="far fa-clock"></i>
                                        <span x-text="item.fecha"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Conceptos registrados (forma de entrega) -->
                            <template x-if="item.conceptos && item.conceptos.length">
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="c in item.conceptos" :key="c.tipo + '_' + c.monto">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700" x-text="`${c.tipo}: ${toMoney(c.monto)}`"></span>
                                    </template>
                                </div>
                            </template>
                            <div class="mt-2 text-sm text-gray-700" x-show="item.cliente">Cliente: <span class="font-medium" x-text="item.cliente"></span></div>
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

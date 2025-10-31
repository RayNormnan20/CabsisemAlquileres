<!-- Contenido específico de Deuda de Alquiler por Edificio -->
<div x-data="{ open:false, selectedNombre:null, selectedDetalle:[], selectedDetalleDepartamentos:[], datos: @entangle('deudaAlquilerPorEdificio'), toMoney(v){ const n = Number(v||0); return new Intl.NumberFormat('es-PE', { style:'currency', currency:'PEN' }).format(n); } }">
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">
                        <i class="fas fa-building mr-1"></i>Edificio
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
                    $datosDeuda = $deudaAlquilerPorEdificio ?? [];
                    $totalCantidad = collect($datosDeuda)->sum('cantidad');
                    $totalMonto = collect($datosDeuda)->sum('monto_total');
                @endphp

                @forelse($datosDeuda as $fila)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                        <button type="button" class="text-blue-700 hover:text-blue-900 font-semibold" @click.prevent="selectedNombre='{{ addslashes($fila['edificio']) }}'; selectedDetalle=(datos||[]).find(d=>d.edificio_id==={{ $fila['edificio_id'] }} )?.detalle||[]; selectedDetalleDepartamentos=(datos||[]).find(d=>d.edificio_id==={{ $fila['edificio_id'] }} )?.departamentos_detalle||[]; open=true" aria-label="Ver deuda de {{ addslashes($fila['edificio']) }}" title="Ver detalle del edificio">
                            {{ $fila['edificio'] }}
                        </button>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center font-semibold">
                        {{ $fila['cantidad'] }}
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-sm font-bold text-blue-600 text-center">
                        S/ {{ number_format($fila['monto_total'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center">
                        <i class="fas fa-file-invoice-dollar text-gray-400 text-4xl mb-3 block"></i>
                        <p class="text-gray-500">No hay registros de deuda actualmente.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(!empty($datosDeuda))
            <tfoot class="bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">TOTAL</th>
                    <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                        {{ $totalCantidad }}
                    </th>
                    <th class="px-2 py-2 text-center text-xs font-medium text-white font-bold">
                        S/ {{ number_format($totalMonto, 2) }}
                    </th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Modal de detalle por edificio (Deuda mensual por departamento) -->
    <div x-show="open" x-transition @keydown.window.escape="open=false" class="fixed inset-0 z-[1000] flex items-center justify-center p-4" style="display: none;">
        <!-- Fondo -->
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="open=false"></div>
        <!-- Contenido -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl mx-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-xl">
                <h3 class="text-base font-semibold">
                    Deuda mensual — <span x-text="selectedNombre"></span>
                </h3>
                <button class="p-2 rounded-full hover:bg-white/20 text-white focus:outline-none z-10" @click="open=false" aria-label="Cerrar modal">
                    <span class="block leading-none text-2xl">&times;</span>
                </button>
            </div>
            <div class="max-h-[70vh] sm:max-h-[75vh] overflow-y-auto overscroll-contain p-4">
                <template x-if="!selectedDetalleDepartamentos || selectedDetalleDepartamentos.length === 0">
                    <p class="text-sm text-gray-500">Sin detalle por departamento disponible.</p>
                </template>
                <div class="space-y-5">
                    <template x-for="(dept, dIdx) in selectedDetalleDepartamentos" :key="dIdx">
                        <div class="rounded-lg border border-gray-200">
                            <div class="px-3 py-2 bg-blue-50 text-sm font-semibold text-blue-800 border-l-4 border-blue-500 flex items-center justify-between" x-text="dept.departamento"></div>
                            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-if="!dept.meses || dept.meses.length === 0">
                                    <p class="text-xs text-gray-500">Sin meses registrados.</p>
                                </template>
                                <template x-for="(m, idx) in (dept.meses||[]).filter(mm => Number(mm.monto||0) > 0)" :key="idx">
                                    <div class="rounded-md px-3 py-2 bg-white hover:bg-gray-50 transition flex items-center justify-between border border-gray-100 shadow-sm">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900" x-text="m.label"></div>
                                        <div class="text-sm sm:text-base font-semibold text-blue-700" x-text="toMoney(m.monto)"></div>
                                    </div>
                                </template>
                                <div class="mt-2 border-t pt-2 flex items-center justify-between">
                                    <div class="text-xs sm:text-sm font-medium text-gray-700">Total depto</div>
                                    <div class="text-sm sm:text-base font-bold text-blue-800" x-text="toMoney((dept.meses||[]).reduce((acc, i)=>acc + Number(i.monto||0), 0))"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="selectedDetalle">
                    <div class="mt-4 border-t pt-3 flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-700">Total deuda del edificio</div>
                        <div class="text-lg font-bold text-blue-800" x-text="toMoney((selectedDetalle||[]).reduce((acc, i)=>acc + Number(i.monto||0), 0))"></div>
                    </div>
                </template>
            </div>
            <div class="px-4 py-3 border-t flex justify-end">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm" @click="open=false">Cerrar</button>
            </div>
        </div>
    </div>
</div>
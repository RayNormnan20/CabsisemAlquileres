<div x-data="dateRangePicker()" x-init="init()" class="relative bg-white p-4 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4">Ingresos y Gastos</h2>

    <div class="relative inline-block text-left w-full md:w-auto" x-data="{ open: false }">
        <button @click="open = !open"
            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md bg-white text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-calendar class="w-4 h-4 text-gray-600" />
            {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d M Y') : 'Desde' }}
            -
            {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d M Y') : 'Hasta' }}
            <svg class="w-4 h-4 ml-1 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" @click.away="open = false" x-transition
            class="absolute z-50 mt-2 w-[370px] rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 p-4 space-y-4">

            <div>
                <label class="block text-sm text-gray-600 mb-1">Período:</label>
                <select wire:model="periodoSeleccionado" wire:change="aplicarPeriodo"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="hoy">Hoy</option>
                    <option value="ayer">Ayer</option>
                    <option value="semana_actual">Esta semana</option>
                    <option value="semana_anterior">Semana pasada</option>
                    <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                    <option value="mes_actual">Este mes</option>
                    <option value="mes_anterior">Mes pasado</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Rango personalizado:</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" wire:model="fechaDesde"
                        class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" />
                    <input type="date" wire:model="fechaHasta"
                        class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>
                @if (!$fechasValidas)
                <div class="text-xs text-red-500 mt-1">⚠️ Fechas no válidas</div>
                @endif
            </div>
            
            <div class="flex justify-end">
                <button @click="open = false" wire:click="validarFechas"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition">
                    Aplicar
                </button>
            </div>
        </div>
    </div>
</div>
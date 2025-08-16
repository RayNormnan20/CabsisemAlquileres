<div x-data="dateRangePicker()" x-init="init()" class="relative bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Ingresos y Gastos</h2>

    <div class="relative inline-block text-left w-full md:w-auto" x-data="{ open: false }">
        <button @click="open = !open"
            class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <x-heroicon-o-calendar class="w-4 h-4 text-gray-600 dark:text-gray-300" />
            {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d M Y') : 'Desde' }}
            -
            {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d M Y') : 'Hasta' }}
            <svg class="w-4 h-4 ml-1 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" @click.away="open = false" x-transition
            class="absolute z-50 mt-2 w-[370px] rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black dark:ring-gray-600 ring-opacity-5 p-4 space-y-4">

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-200 mb-1">Período:</label>
                <select wire:model="periodoSeleccionado" wire:change="aplicarPeriodo"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
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
                <label class="block text-sm text-gray-600 dark:text-gray-200 mb-1">Rango personalizado:</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" wire:model="fechaDesde"
                        class="border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500" />
                    <input type="date" wire:model="fechaHasta"
                        class="border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                @if (!$fechasValidas)
                <div class="text-xs text-red-500 mt-1">⚠️ Fechas no válidas</div>
                @endif
            </div>
        </div>
    </div>
</div>
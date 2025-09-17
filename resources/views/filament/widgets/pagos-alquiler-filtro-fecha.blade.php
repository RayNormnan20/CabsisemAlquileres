<div x-data="dateRangePicker()" x-init="init()" class="w-full bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Filtrar Pagos de Alquiler</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-1">
        <!-- Filtro de fecha (izquierda) -->
        <div class="relative inline-block text-left w-full" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                @php
                \Carbon\Carbon::setLocale('es');
                @endphp
                {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->translatedFormat('d M Y') : 'Desde' }}
                -
                {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->translatedFormat('d M Y') : 'Hasta' }}
                <svg class="w-4 h-4 ml-1 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                class="absolute z-50 mt-2 w-full max-w-md rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black dark:ring-gray-600 ring-opacity-5 p-4 space-y-4">

                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-200 mb-1">Período:</label>
                    <select wire:model="periodoSeleccionado" wire:change="aplicarPeriodo"
                         class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                         <option value="hoy">Hoy</option>
                         <option value="ayer">Ayer</option>
                         <option value="esta_semana">Esta semana</option>
                         <option value="semana_pasada">Semana pasada</option>
                         <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                         <option value="este_mes">Este mes</option>
                         <option value="mes_pasado">Mes pasado</option>
                         <option value="personalizado">Personalizado</option>
                     </select>
                </div>

                <div x-show="$wire.periodoSeleccionado === 'personalizado'">
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

                <div class="flex justify-between items-center pt-2">
                    <button wire:click="limpiarFiltros"
                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        Limpiar filtros
                    </button>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Filtrar por fecha de pago
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros de búsqueda (derecha) -->
        <x-filament::card>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Filtros de Búsqueda
                    </h3>

                    <x-filament::button color="success" icon="heroicon-o-document-download" wire:click="exportarPdf" size="sm">
                        Exportar PDF
                    </x-filament::button>
                </div>
            </div>

            {{ $this->form }}
        </x-filament::card>
    </div>
</div>

<script>
function dateRangePicker() {
    return {
        init() {
            // Inicialización si es necesaria
        }
    }
}
</script>

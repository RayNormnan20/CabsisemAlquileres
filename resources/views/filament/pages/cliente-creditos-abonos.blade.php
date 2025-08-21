<x-filament::page>
    <div class="space-y-6">
        <!-- Formulario de selección de usuario con botones de exportación -->
        <x-filament::card>
            <div class="p-4">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Seleccione un Usuario</h2>
                    
                    <!-- Botones de exportación (solo visibles cuando hay usuario seleccionado) -->
                    @if($this->rutaId)
                        <div class="flex gap-2">
                            <!-- Botón PDF -->
                            <button wire:click="exportToPDF" wire:loading.attr="disabled" wire:target="exportToPDF" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span wire:loading.remove wire:target="exportToPDF">PDF</span>
                                <span wire:loading wire:target="exportToPDF">Generando...</span>
                            </button>
                            
                            <!-- Botón Excel -->
                            <button wire:click.prevent class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </button>
                        </div>
                    @endif
                </div>
                
                {{ $this->form }}
                
                <!-- Filtros de Fechas -->
                @if($this->rutaId)
                    <!-- Filtros de Fechas para Exportación -->
                    <div x-data="dateRangePicker()" x-init="init()" class="relative bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h2 class="text-xl font-bold text-primary-600 mb-0">Filtros de Fechas</h2>
                            
                            <div class="relative inline-block text-left w-full md:w-auto" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    <x-heroicon-o-calendar class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                                    {{ $this->fechaDesde ? \Carbon\Carbon::parse($this->fechaDesde)->format('d M Y') : 'Desde' }}
                                    -
                                    {{ $this->fechaHasta ? \Carbon\Carbon::parse($this->fechaHasta)->format('d M Y') : 'Hasta' }}
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

                                    <div class="flex justify-between items-center pt-2">
                                        <button wire:click="limpiarFiltros" 
                                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                            Limpiar filtros
                                        </button>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Los filtros se aplicarán tanto a créditos como a abonos en la exportación PDF.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::card>
        
        <!-- Widget de créditos y abonos -->
        <div>
            @if($this->rutaId)
                @livewire(\App\Filament\Widgets\ClienteCreditosAbonosWidget::class, [
                    'wire:key' => "ruta-creditos-abonos-{$this->rutaId}",
                    'rutaId' => $this->rutaId,
                    'fechaDesde' => $this->fechaDesde,
                    'fechaHasta' => $this->fechaHasta,
                    'periodoSeleccionado' => $this->periodoSeleccionado,
                ])
            @endif
        </div>
    </div>
</x-filament::page>

<script>
    function dateRangePicker() {
        return {
            init() {
                // Inicialización del componente
            }
        }
    }
</script>
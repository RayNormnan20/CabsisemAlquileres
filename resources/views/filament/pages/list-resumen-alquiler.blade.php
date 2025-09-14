<x-filament::page>
    <div class="space-y-6">
        <!-- Filtros de Edificio y Departamento -->
        <x-filament::card>
            <div class="p-1">
                <!-- Header con botón de exportar -->
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Resumen de Alquiler
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Selecciona un edificio y departamento para ver el resumen detallado
                        </p>
                    </div>

                    <!-- Botón de exportar PDF
                    @if($this->selectedEdificio && $this->selectedDepartamento)
                        <div>
                            <button wire:click="exportToPDF" wire:loading.attr="disabled" wire:target="exportToPDF"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span wire:loading.remove wire:target="exportToPDF">Exportar PDF</span>
                                <span wire:loading wire:target="exportToPDF">Generando...</span>
                            </button>
                        </div>
                    @endif
                    -->
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edificio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Edificio
                        </label>
                        <select wire:model="selectedEdificio" id="edificio"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Seleccionar edificio</option>
                            @foreach($this->getEdificios() as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="departamento"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Departamento
                        </label>
                        <select wire:model="selectedDepartamento" id="departamento"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            {{ !$this->selectedEdificio ? 'disabled' : '' }}>
                            <option value="">Seleccionar departamento</option>
                            @foreach($this->getDepartamentos() as $id => $numero)
                            <option value="{{ $id }}">{{ $numero }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <!-- Tabla de Pagos Mensuales -->
        <x-filament::card>
            <div class="p-1">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Resumen de Pagos Mensuales
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    MES
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    TOTAL
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    PAGADO
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    ESTADO
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @if($this->selectedEdificio && $this->selectedDepartamento)
                            @forelse($this->pagosMensuales as $pago)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $pago['mes'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                    S/ {{ number_format($pago['total'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                    S/ {{ number_format($pago['pagado'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pago['estado'] === 'CANCELADO')
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        {{ $pago['estado'] }}
                                    </span>
                                    @elseif($pago['estado'] === 'DEUDA PENDIENTE')
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        {{ $pago['estado'] }}
                                    </span>
                                    @elseif($pago['estado'] === 'PAGO PARCIAL')
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100">
                                        {{ $pago['estado'] }}
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                        {{ $pago['estado'] }}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay datos disponibles para la selección actual
                                </td>
                            </tr>
                            @endforelse

                            @if(count($this->pagosMensuales) > 0)
                            <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    TOTAL ABONOS
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    S/ {{ number_format($this->totalAbonos, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ count(array_filter($this->pagosMensuales, fn($p) => $p['estado'] === 'CANCELADO')) }}
                                        de {{ count($this->pagosMensuales) }} pagos
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @else
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Selecciona un edificio y departamento para ver los datos
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>

        <!-- Tabla de Detalles de Pagos -->
        <x-filament::card>
            <div class="p-1">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Detalles de Pagos Realizados
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    CLIENTE
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    FECHA
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    COBRADOR
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    ABONO
                                </th>
                                <!--
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    DETALLE
                                </th>
                                -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    FOTO ALQUILER
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @if($this->selectedEdificio && $this->selectedDepartamento)
                            @forelse($this->detallesPagos as $detalle)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $detalle['cliente_nombre'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $detalle['cobrador_nombre'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    S/ {{ number_format($detalle['monto_pagado'], 2) }}
                                </td>
                                <!--
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $detalle['observaciones'] ?? 'Sin observaciones' }}
                                </td>
                                -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="flex items-center space-x-1">
                                        @if($detalle['imagen_1_path'])
                                        <img src="{{ asset('storage/' . $detalle['imagen_1_path']) }}" alt="Imagen 1"
                                            class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                            onclick="window.open('{{ asset('storage/' . $detalle['imagen_1_path']) }}', '_blank')"
                                            title="Click para ver Imagen 1">
                                        @endif
                                        @if($detalle['imagen_2_path'])
                                        <img src="{{ asset('storage/' . $detalle['imagen_2_path']) }}" alt="Imagen 2"
                                            class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                            onclick="window.open('{{ asset('storage/' . $detalle['imagen_2_path']) }}', '_blank')"
                                            title="Click para ver Imagen 2">
                                        @endif
                                        @if($detalle['imagen_3_path'])
                                        <img src="{{ asset('storage/' . $detalle['imagen_3_path']) }}" alt="Imagen 3"
                                            class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                            onclick="window.open('{{ asset('storage/' . $detalle['imagen_3_path']) }}', '_blank')"
                                            title="Click para ver Imagen 3">
                                        @endif
                                        @if(!$detalle['imagen_1_path'] && !$detalle['imagen_2_path'] &&
                                        !$detalle['imagen_3_path'])
                                        <span class="text-gray-400 text-xs">Sin fotos</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay pagos registrados para la selección actual
                                </td>
                            </tr>
                            @endforelse
                            @else
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Selecciona un edificio y departamento para ver los detalles
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
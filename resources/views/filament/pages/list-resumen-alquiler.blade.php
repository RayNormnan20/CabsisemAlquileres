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

                <style>
                    .resumen-pagos-responsive {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }

                    /* Diseño para móviles - Cards como historial de abonos */
                    .mobile-resumen-container {
                        display: block;
                        padding: 1rem;
                        background-color: #f8f9fa;
                        border-radius: 12px;
                        overflow: hidden;
                        width: 100%;
                        max-width: 100%;
                    }

                    .resumen-item {
                        background: white;
                        margin-bottom: 0.75rem;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        overflow: hidden;
                    }

                    .resumen-header {
                        background: #f8f9fa;
                        border-bottom: 1px solid #e9ecef;
                        padding: 0.75rem 1rem;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .resumen-mes {
                        font-weight: 600;
                        font-size: 0.9rem;
                        color: #2c3e50;
                    }

                    .resumen-estado {
                        font-size: 0.75rem;
                        font-weight: 600;
                        padding: 0.25rem 0.5rem;
                        border-radius: 9999px;
                    }

                    .estado-cancelado {
                        background-color: #d4edda;
                        color: #155724;
                    }

                    .estado-deuda {
                        background-color: #f8d7da;
                        color: #721c24;
                    }

                    .estado-parcial {
                        background-color: #fff3cd;
                        color: #856404;
                    }

                    .estado-otro {
                        background-color: #ffeaa7;
                        color: #6c5ce7;
                    }

                    .resumen-body {
                        padding: 0.75rem 1rem;
                    }

                    .resumen-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 0.5rem;
                        font-size: 0.85rem;
                    }

                    .resumen-label {
                        color: #7f8c8d;
                        font-weight: 500;
                    }

                    .resumen-value {
                        color: #2c3e50;
                        font-weight: 600;
                    }

                    .resumen-total-card {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        margin-top: 1rem;
                        border-radius: 12px;
                        padding: 1rem;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    }

                    .resumen-total-title {
                        font-size: 1rem;
                        font-weight: 700;
                        margin-bottom: 0.5rem;
                        text-align: center;
                    }

                    .resumen-total-amount {
                        font-size: 1.25rem;
                        font-weight: 800;
                        text-align: center;
                        margin-bottom: 0.5rem;
                    }

                    .resumen-total-stats {
                        font-size: 0.85rem;
                        text-align: center;
                        opacity: 0.9;
                    }

                    .empty-state-resumen {
                        text-align: center;
                        padding: 2rem;
                        color: #7f8c8d;
                    }

                    /* Tabla para PC - oculta en móvil */
                    .desktop-resumen-table {
                        display: none;
                    }

                    /* Media queries para responsividad */
                    @media (min-width: 768px) {
                        .mobile-resumen-container {
                            display: none;
                        }
                        
                        .desktop-resumen-table {
                            display: block;
                        }
                    }
                </style>

                <div class="resumen-pagos-responsive">
                    <!-- Diseño para móviles - Cards -->
                    <div class="mobile-resumen-container">
                        @if($this->selectedEdificio && $this->selectedDepartamento)
                            @forelse($this->pagosMensuales as $pago)
                                <div class="resumen-item">
                                    <div class="resumen-header">
                                        <div class="resumen-mes">{{ $pago['mes'] }}</div>
                                        <div class="resumen-estado 
                                            @if($pago['estado'] === 'CANCELADO') estado-cancelado
                                            @elseif($pago['estado'] === 'DEUDA PENDIENTE') estado-deuda
                                            @elseif($pago['estado'] === 'PAGO PARCIAL') estado-parcial
                                            @else estado-otro
                                            @endif">
                                            {{ $pago['estado'] }}
                                        </div>
                                    </div>
                                    <div class="resumen-body">
                                        <div class="resumen-info">
                                            <span class="resumen-label">Total:</span>
                                            <span class="resumen-value">S/ {{ number_format($pago['total'], 2) }}</span>
                                        </div>
                                        <div class="resumen-info">
                                            <span class="resumen-label">Pagado:</span>
                                            <span class="resumen-value">S/ {{ number_format($pago['pagado'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state-resumen">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Sin datos disponibles</div>
                                    <div>No hay datos disponibles para la selección actual.</div>
                                </div>
                            @endforelse

                            @if(count($this->pagosMensuales) > 0)
                                <div class="resumen-total-card">
                                    <div class="resumen-total-title">TOTAL ABONOS</div>
                                    <div class="resumen-total-amount">S/ {{ number_format($this->totalAbonos, 2) }}</div>
                                    <div class="resumen-total-stats">
                                        {{ count(array_filter($this->pagosMensuales, fn($p) => $p['estado'] === 'CANCELADO')) }}
                                        de {{ count($this->pagosMensuales) }} pagos completados
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="empty-state-resumen">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🏢</div>
                                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Selecciona edificio y departamento</div>
                                <div>Selecciona un edificio y departamento para ver los datos.</div>
                            </div>
                        @endif
                    </div>

                    <!-- Diseño para PC - Tabla original -->
                    <div class="desktop-resumen-table overflow-x-auto">
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

                <style>
                    .detalles-pagos-responsive {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }

                    /* Diseño para móviles - Cards como historial de abonos */
                    .mobile-cards-container {
                        display: block;
                        padding: 1rem;
                        background-color: #f8f9fa;
                        border-radius: 12px;
                        overflow: hidden;
                        width: 100%;
                        max-width: 100%;
                    }

                    .pago-item {
                        background: white;
                        margin-bottom: 0.75rem;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        overflow: hidden;
                    }

                    .pago-header {
                        background: #f8f9fa;
                        border-bottom: 1px solid #e9ecef;
                        padding: 0.75rem 1rem;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .pago-cliente {
                        font-weight: 600;
                        font-size: 0.9rem;
                        color: #2c3e50;
                    }

                    .pago-monto {
                        font-weight: 700;
                        font-size: 1rem;
                        color: #27ae60;
                    }

                    .pago-body {
                        padding: 0.75rem 1rem;
                    }

                    .pago-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 0.5rem;
                        font-size: 0.85rem;
                    }

                    .pago-label {
                        color: #7f8c8d;
                        font-weight: 500;
                    }

                    .pago-value {
                        color: #2c3e50;
                        font-weight: 600;
                    }

                    .pago-fotos {
                        margin-top: 0.5rem;
                        padding-top: 0.5rem;
                        border-top: 1px solid #e9ecef;
                    }

                    .fotos-container {
                        display: flex;
                        gap: 0.5rem;
                        align-items: center;
                    }

                    .foto-alquiler {
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 1px solid #e9ecef;
                        cursor: pointer;
                        transition: transform 0.2s;
                    }

                    .foto-alquiler:hover {
                        transform: scale(1.1);
                    }

                    .empty-state {
                        text-align: center;
                        padding: 2rem;
                        color: #7f8c8d;
                    }

                    /* Tabla para PC - oculta en móvil */
                    .desktop-table {
                        display: none;
                    }

                    /* Media queries para responsividad */
                    @media (min-width: 768px) {
                        .mobile-cards-container {
                            display: none;
                        }
                        
                        .desktop-table {
                            display: block;
                        }
                    }
                </style>

                <div class="detalles-pagos-responsive">
                    <!-- Diseño para móviles - Cards -->
                    <div class="mobile-cards-container">
                        @if($this->selectedEdificio && $this->selectedDepartamento)
                            @forelse($this->detallesPagos as $detalle)
                                <div class="pago-item">
                                    <div class="pago-header">
                                        <div class="pago-cliente">{{ $detalle['cliente_nombre'] ?? 'N/A' }}</div>
                                        <div class="pago-monto">S/ {{ number_format($detalle['monto_pagado'], 2) }}</div>
                                    </div>
                                    <div class="pago-body">
                                        <div class="pago-info">
                                            <span class="pago-label">Fecha:</span>
                                            <span class="pago-value">{{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="pago-info">
                                            <span class="pago-label">Cobrador:</span>
                                            <span class="pago-value">{{ $detalle['cobrador_nombre'] ?? 'N/A' }}</span>
                                        </div>
                                        @if($detalle['imagen_1_path'] || $detalle['imagen_2_path'] || $detalle['imagen_3_path'])
                                        <div class="pago-fotos">
                                            <div class="pago-info" style="margin-bottom: 0.25rem;">
                                                <span class="pago-label">Fotos del Alquiler:</span>
                                            </div>
                                            <div class="fotos-container">
                                                @if($detalle['imagen_1_path'])
                                                <img src="{{ asset('storage/' . $detalle['imagen_1_path']) }}" alt="Imagen 1"
                                                    class="foto-alquiler"
                                                    onclick="window.open('{{ asset('storage/' . $detalle['imagen_1_path']) }}', '_blank')"
                                                    title="Click para ver Imagen 1">
                                                @endif
                                                @if($detalle['imagen_2_path'])
                                                <img src="{{ asset('storage/' . $detalle['imagen_2_path']) }}" alt="Imagen 2"
                                                    class="foto-alquiler"
                                                    onclick="window.open('{{ asset('storage/' . $detalle['imagen_2_path']) }}', '_blank')"
                                                    title="Click para ver Imagen 2">
                                                @endif
                                                @if($detalle['imagen_3_path'])
                                                <img src="{{ asset('storage/' . $detalle['imagen_3_path']) }}" alt="Imagen 3"
                                                    class="foto-alquiler"
                                                    onclick="window.open('{{ asset('storage/' . $detalle['imagen_3_path']) }}', '_blank')"
                                                    title="Click para ver Imagen 3">
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Sin pagos registrados</div>
                                    <div>No hay pagos registrados para la selección actual.</div>
                                </div>
                            @endforelse
                        @else
                            <div class="empty-state">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🏢</div>
                                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Selecciona edificio y departamento</div>
                                <div>Selecciona un edificio y departamento para ver los detalles.</div>
                            </div>
                        @endif
                    </div>

                    <!-- Diseño para PC - Tabla original -->
                    <div class="desktop-table overflow-x-auto">
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
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        FOTOS PAGO
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <div class="flex items-center space-x-1">
                                            @if($detalle['foto_1_path'])
                                            <img src="{{ asset('storage/' . $detalle['foto_1_path']) }}" alt="Foto Pago 1"
                                                class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                                onclick="window.open('{{ asset('storage/' . $detalle['foto_1_path']) }}', '_blank')"
                                                title="Click para ver Foto del Pago 1">
                                            @endif
                                            @if($detalle['foto_2_path'])
                                            <img src="{{ asset('storage/' . $detalle['foto_2_path']) }}" alt="Foto Pago 2"
                                                class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                                onclick="window.open('{{ asset('storage/' . $detalle['foto_2_path']) }}', '_blank')"
                                                title="Click para ver Foto del Pago 2">
                                            @endif
                                            @if($detalle['foto_3_path'])
                                            <img src="{{ asset('storage/' . $detalle['foto_3_path']) }}" alt="Foto Pago 3"
                                                class="h-[18px] w-[18px] rounded-full object-cover border border-gray-200 cursor-pointer hover:scale-110 transition-transform"
                                                onclick="window.open('{{ asset('storage/' . $detalle['foto_3_path']) }}', '_blank')"
                                                title="Click para ver Foto del Pago 3">
                                            @endif
                                            @if(!$detalle['foto_1_path'] && !$detalle['foto_2_path'] &&
                                            !$detalle['foto_3_path'])
                                            <span class="text-gray-400 text-xs">Sin fotos</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay pagos registrados para la selección actual
                                    </td>
                                </tr>
                                @endforelse
                                @else
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Selecciona un edificio y departamento para ver los detalles
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
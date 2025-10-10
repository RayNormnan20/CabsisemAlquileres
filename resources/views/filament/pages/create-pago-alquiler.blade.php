<x-filament::page>
    <!-- Elemento raíz único para Livewire -->
    <div>

        
        <div class="space-y-6">
            <!-- Formulario principal -->
            <div>
                {{ $this->form }}
                
                <!-- Acciones de Filament (botón Crear) -->
                <div class="mt-6">
                    <x-filament::form.actions 
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </div>
            </div>

            <!-- Footer con resumen dinámico -->
        <div id="resumen-footer" style="display: block; border: 2px solid red; padding: 2px; margin: 2px;" wire:loading.remove wire:key="resumen-footer">
            
            @if($selectedAlquilerId && count($pagosMensuales) > 0)
            <div class="space-y-4" style="margin: 0; padding: 0;">
                <!-- Resumen de Pagos Mensuales -->
                <x-filament::card>
                    <div class="p-0">
                        <div class="mb-4 px-4 pt-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Resumen de Pagos Mensuales
                            </h3>
                        </div>

                        <style>
                            .resumen-pagos-responsive {
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            }

                            .mobile-resumen-container {
                                display: block;
                                padding: 0;
                                background-color: transparent;
                                border-radius: 0;
                                overflow: hidden;
                                width: 100%;
                                max-width: 100%;
                                margin: 0;
                            }

                            .resumen-item {
                                background: white;
                                margin: 0 0 0.75rem 0;
                                border-radius: 8px;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                                overflow: hidden;
                                width: 100%;
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
                                font-weight: 600;
                                font-size: 0.8rem;
                                padding: 0.25rem 0.5rem;
                                border-radius: 9999px;
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

                            .desktop-resumen-table {
                                display: none;
                            }

                            .total-abonos-card {
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                margin-top: 1rem;
                                border-radius: 12px;
                                padding: 1rem;
                                text-align: center;
                            }

                            .total-abonos-amount {
                                font-size: 1.5rem;
                                font-weight: 700;
                                margin-bottom: 0.5rem;
                            }

                            .total-abonos-text {
                                font-size: 0.9rem;
                                opacity: 0.9;
                            }

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
                                @forelse($pagosMensuales as $pago)
                                    <div class="resumen-item">
                                        <div class="resumen-header">
                                            <div class="resumen-mes">{{ $pago['mes'] }}</div>
                                            <div class="resumen-estado 
                                                @if($pago['estado'] === 'CANCELADO') bg-green-100 text-green-800
                                                @elseif($pago['estado'] === 'DEUDA PENDIENTE') bg-red-100 text-red-800
                                                @elseif($pago['estado'] === 'PAGO PARCIAL') bg-orange-100 text-orange-800
                                                @else bg-yellow-100 text-yellow-800
                                                @endif">
                                                {{ $pago['estado'] }}
                                            </div>
                                        </div>
                                        <div class="resumen-body">
                                            <div class="resumen-info">
                                                <span class="resumen-label">Total del Mes:</span>
                                                <span class="resumen-value">S/ {{ number_format($pago['total'], 2) }}</span>
                                            </div>
                                            <div class="resumen-info">
                                                <span class="resumen-label">Pagado:</span>
                                                <span class="resumen-value" style="color: #27ae60; font-weight: 700;">S/ {{ number_format($pago['pagado'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-gray-500">
                                        No hay datos disponibles
                                    </div>
                                @endforelse

                                @if(count($pagosMensuales) > 0)
                                    <div class="total-abonos-card">
                                        <div class="total-abonos-amount">S/ {{ number_format($totalAbonos, 2) }}</div>
                                        <div class="total-abonos-text">
                                            Total de Abonos<br>
                                            {{ count(array_filter($pagosMensuales, fn($p) => $p['estado'] === 'CANCELADO')) }} de {{ count($pagosMensuales) }} pagos completados
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Diseño para PC - Tabla -->
                            <div class="desktop-resumen-table">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    MES
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    TOTAL
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    PAGADO
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    ESTADO
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse($pagosMensuales as $pago)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $pago['mes'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    S/ {{ number_format($pago['total'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                                    S/ {{ number_format($pago['pagado'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($pago['estado'] === 'CANCELADO')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                        {{ $pago['estado'] }}
                                                    </span>
                                                    @elseif($pago['estado'] === 'DEUDA PENDIENTE')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                        {{ $pago['estado'] }}
                                                    </span>
                                                    @elseif($pago['estado'] === 'PAGO PARCIAL')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100">
                                                        {{ $pago['estado'] }}
                                                    </span>
                                                    @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                        {{ $pago['estado'] }}
                                                    </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No hay datos disponibles
                                                </td>
                                            </tr>
                                            @endforelse

                                            @if(count($pagosMensuales) > 0)
                                            <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                                    TOTAL ABONOS
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                                    S/ {{ number_format($totalAbonos, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                        {{ count(array_filter($pagosMensuales, fn($p) => $p['estado'] === 'CANCELADO')) }}
                                                        de {{ count($pagosMensuales) }} pagos
                                                    </span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                <!-- Detalles de Pagos Realizados -->
                <x-filament::card>
                    <div class="p-0">
                        <div class="mb-4 px-4 pt-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Detalles de Pagos Realizados
                            </h3>
                        </div>

                        <style>
                            .detalles-pagos-responsive {
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            }

                            .mobile-cards-container {
                                display: block;
                                padding: 0;
                                background-color: transparent;
                                border-radius: 0;
                                overflow: hidden;
                                width: 100%;
                                max-width: 100%;
                                margin: 0;
                            }

                            .pago-item {
                                background: white;
                                margin: 0 0 0.75rem 0;
                                border-radius: 8px;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                                overflow: hidden;
                                width: 100%;
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

                            .desktop-table {
                                display: none;
                            }

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
                                @forelse($detallesPagos as $detalle)
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
                                                <span class="pago-label">Mes/Año:</span>
                                                <span class="pago-value">{{ str_pad($detalle['mes_correspondiente'], 2, '0', STR_PAD_LEFT) }}/{{ $detalle['ano_correspondiente'] }}</span>
                                            </div>
                                            <div class="pago-info">
                                                <span class="pago-label">Método:</span>
                                                <span class="pago-value">{{ ucfirst($detalle['metodo_pago']) }}</span>
                                            </div>
                                            <div class="pago-info">
                                                <span class="pago-label">Cobrador:</span>
                                                <span class="pago-value">{{ $detalle['cobrador_nombre'] }}</span>
                                            </div>
                                            @if($detalle['observaciones'])
                                            <div class="pago-info">
                                                <span class="pago-label">Observaciones:</span>
                                                <span class="pago-value">{{ $detalle['observaciones'] }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-gray-500">
                                        No hay pagos registrados para este alquiler
                                    </div>
                                @endforelse
                            </div>

                            <!-- Diseño para PC - Tabla -->
                            <div class="desktop-table">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CLIENTE</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">FECHA</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MES/AÑO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MONTO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MÉTODO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">COBRADOR</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OBSERVACIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse($detallesPagos as $detalle)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $detalle['cliente_nombre'] ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($detalle['fecha_pago'])->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ str_pad($detalle['mes_correspondiente'], 2, '0', STR_PAD_LEFT) }}/{{ $detalle['ano_correspondiente'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                                    S/ {{ number_format($detalle['monto_pagado'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ ucfirst($detalle['metodo_pago']) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $detalle['cobrador_nombre'] }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    {{ $detalle['observaciones'] ?? '-' }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No hay pagos registrados para este alquiler
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::card>
            </div>
            @endif
        </div>
    </div>

    <!-- JavaScript para mostrar/ocultar el footer -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - Iniciando script del footer');
            
            // Variable para rastrear el último estado del footer
            let lastFooterState = null;
            let lastAlquilerId = null;
            let lastPagosMensualesCount = null;

            // Función para actualizar el contenido del footer dinámicamente
            function updateFooterContent(data) {
                const footer = document.getElementById('resumen-footer');
                if (!footer || !data.pagosMensuales || !data.detallesPagos) {
                    console.log('⚠️ No se puede actualizar el footer - datos insuficientes');
                    return;
                }
                
                console.log('🔄 Actualizando contenido del footer con nuevos datos');
                console.log('📊 Datos recibidos:', data);
                
                // Generar HTML para pagos mensuales
                let pagosMensualesHTML = '';
                data.pagosMensuales.forEach(pago => {
                    
                    const estadoClass = pago.estado === 'CANCELADO' ? 'bg-green-100 text-green-800' :
                                       pago.estado === 'PAGO PARCIAL' ? 'bg-yellow-100 text-yellow-800' :
                                       pago.estado === 'DEUDA PENDIENTE' ? 'bg-red-100 text-red-800' :
                                       'bg-gray-100 text-gray-800';
                    
                    // Usar los datos correctos del servidor
                    const mesNombre = pago.mes || 'Mes desconocido';
                    const totalMes = pago.total ? parseFloat(pago.total) : 0;
                    const pagadoMes = pago.pagado ? parseFloat(pago.pagado) : 0;
                    const estado = pago.estado || 'DEUDA PENDIENTE';
                    
                    pagosMensualesHTML += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                ${mesNombre}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                S/ ${totalMes.toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                S/ ${pagadoMes.toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${estadoClass}">
                                    ${estado}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                
                // Array de nombres de meses en español
                const mesesNombres = [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                
                // Generar HTML para detalles de pagos
                let detallesPagosHTML = '';
                data.detallesPagos.forEach(detalle => {
                    
                    // Validar y procesar datos de detalle
                    const fechaPago = detalle.fecha_pago ? new Date(detalle.fecha_pago) : new Date();
                    const fechaFormateada = fechaPago.toLocaleDateString('es-PE');
                    const clienteNombre = detalle.cliente_nombre || 'N/A';
                    const mesCorrespondiente = detalle.mes_correspondiente ? parseInt(detalle.mes_correspondiente) : 1;
                    const anoCorrespondiente = detalle.ano_correspondiente ? parseInt(detalle.ano_correspondiente) : new Date().getFullYear();
                    const mesNombreDetalle = mesesNombres[mesCorrespondiente - 1] || 'Mes';
                    const montoPagado = detalle.monto_pagado ? parseFloat(detalle.monto_pagado) : 0;
                    const metodoPago = detalle.metodo_pago ? detalle.metodo_pago.charAt(0).toUpperCase() + detalle.metodo_pago.slice(1) : 'N/A';
                    const cobradorNombre = detalle.cobrador_nombre || 'N/A';
                    const observaciones = detalle.observaciones || '-';
                    
                    detallesPagosHTML += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                ${clienteNombre}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${fechaFormateada}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${mesNombreDetalle} ${anoCorrespondiente}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                S/ ${montoPagado.toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${metodoPago}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${cobradorNombre}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                ${observaciones}
                            </td>
                        </tr>
                    `;
                });
                
                // Generar HTML para cards móviles de resumen
                let resumenCardsHTML = '';
                if (pagosMensuales && pagosMensuales.length > 0) {
                    resumenCardsHTML = pagosMensuales.map(pago => {
                        let estadoClass = '';
                        if (pago.estado === 'CANCELADO') {
                            estadoClass = 'bg-green-100 text-green-800';
                        } else if (pago.estado === 'DEUDA PENDIENTE') {
                            estadoClass = 'bg-red-100 text-red-800';
                        } else if (pago.estado === 'PAGO PARCIAL') {
                            estadoClass = 'bg-orange-100 text-orange-800';
                        } else {
                            estadoClass = 'bg-yellow-100 text-yellow-800';
                        }
                        
                        return `
                            <div class="resumen-item">
                                <div class="resumen-header">
                                    <div class="resumen-mes">${pago.mes}</div>
                                    <div class="resumen-estado ${estadoClass}">
                                        ${pago.estado}
                                    </div>
                                </div>
                                <div class="resumen-body">
                                    <div class="resumen-info">
                                        <span class="resumen-label">Total del Mes:</span>
                                        <span class="resumen-value">S/ ${parseFloat(pago.total).toFixed(2)}</span>
                                    </div>
                                    <div class="resumen-info">
                                        <span class="resumen-label">Pagado:</span>
                                        <span class="resumen-value" style="color: #27ae60; font-weight: 700;">S/ ${parseFloat(pago.pagado).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    
                    // Calcular total de abonos
                    const totalAbonos = pagosMensuales.reduce((sum, pago) => sum + parseFloat(pago.pagado || 0), 0);
                    const pagosCompletados = pagosMensuales.filter(p => p.estado === 'CANCELADO').length;
                    
                    resumenCardsHTML += `
                        <div class="total-abonos-card">
                            <div class="total-abonos-amount">S/ ${totalAbonos.toFixed(2)}</div>
                            <div class="total-abonos-text">
                                Total de Abonos<br>
                                ${pagosCompletados} de ${pagosMensuales.length} pagos completados
                            </div>
                        </div>
                    `;
                } else {
                    resumenCardsHTML = '<div class="text-center py-4 text-gray-500">No hay datos disponibles</div>';
                }

                // Actualizar el contenido completo del footer
                footer.innerHTML = `
                    <style>
                        .resumen-pagos-responsive {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        }

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
                            font-weight: 600;
                            font-size: 0.8rem;
                            padding: 0.25rem 0.5rem;
                            border-radius: 9999px;
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

                        .desktop-resumen-table {
                            display: none;
                        }

                        .total-abonos-card {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            margin-top: 1rem;
                            border-radius: 12px;
                            padding: 1rem;
                            text-align: center;
                        }

                        .total-abonos-amount {
                            font-size: 1.5rem;
                            font-weight: 700;
                            margin-bottom: 0.5rem;
                        }

                        .total-abonos-text {
                            font-size: 0.9rem;
                            opacity: 0.9;
                        }

                        @media (min-width: 768px) {
                            .mobile-resumen-container {
                                display: none;
                            }
                            
                            .desktop-resumen-table {
                                display: block;
                            }
                        }
                    </style>
                    <div class="space-y-6">
                        <!-- Resumen de Pagos Mensuales -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div class="p-6">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Resumen de Pagos Mensuales
                                    </h3>
                                </div>
                                
                                <div class="resumen-pagos-responsive">
                                    <!-- Diseño para móviles - Cards -->
                                    <div class="mobile-resumen-container">
                                        ${resumenCardsHTML}
                                    </div>

                                    <!-- Diseño para PC - Tabla -->
                                    <div class="desktop-resumen-table">
                                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                                <thead class="bg-gray-50 dark:bg-gray-800">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MES</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">TOTAL</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PAGADO</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ESTADO</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                                    ${pagosMensualesHTML || '<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos de pagos mensuales</td></tr>'}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detalles de Pagos -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div class="p-6">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Detalles de Pagos Realizados
                                    </h3>
                                </div>
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CLIENTE</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">FECHA PAGO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MES CORRESPONDIENTE</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MONTO PAGADO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MÉTODO</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">COBRADOR</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OBSERVACIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            ${detallesPagosHTML || '<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No hay detalles de pagos</td></tr>'}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
            }



            // Función para mostrar/ocultar el footer basado en los datos
            function showFooterIfHasData() {
                const footer = document.getElementById('resumen-footer');
                const createButton = document.getElementById('create-pago-button');
                
                if (!footer) return;

                const hasData = selectedAlquilerId && pagosMensuales && pagosMensuales.length > 0;
                const currentState = {
                    alquilerId: selectedAlquilerId,
                    pagosMensualesCount: pagosMensuales ? pagosMensuales.length : 0,
                    hasData: hasData
                };

                // Solo actualizar si el estado ha cambiado
                if (JSON.stringify(currentState) !== JSON.stringify(lastFooterState)) {
                    lastFooterState = currentState;
                    
                    if (hasData) {
                        footer.style.display = 'block';
                    } else {
                        footer.style.display = 'none';
                    }
                }

                // Mostrar/ocultar botón de crear pago
                if (createButton) {
                    if (selectedAlquilerId) {
                        createButton.style.display = 'block';
                    } else {
                        createButton.style.display = 'none';
                    }
                }
            }

            // Función para configurar el listener del select de alquiler
            function setupAlquilerListener() {
                
                // Lista de selectores posibles para el select de alquiler
                const possibleSelectors = [
                    'select[wire\\:model="data.alquiler_id"]',
                    'select[name="data.alquiler_id"]',
                    'select[wire\\:model="alquiler_id"]',
                    'select[name="alquiler_id"]',
                    '#data\\.alquiler_id',
                    'select[id*="alquiler"]',
                    'select[name*="alquiler"]'
                ];

                let alquilerSelect = null;

                // Intentar encontrar el select con los selectores conocidos
                for (const selector of possibleSelectors) {
                    try {
                        const element = document.querySelector(selector);
                        if (element && !element.closest('.fi-modal')) {
                            alquilerSelect = element;
                            break;
                        } else if (element && element.closest('.fi-modal')) {
                            // Skip modal selects
                        }
                    } catch (error) {
                        // Selector inválido, continuar
                    }
                }

                // Si no se encontró, buscar manualmente
                if (!alquilerSelect) {
                    const allSelects = document.querySelectorAll('select');
                    
                    allSelects.forEach((select, index) => {
                        const wireModel = select.getAttribute('wire:model');
                        const name = select.name;
                        const id = select.id;
                        
                        // Verificar si es el select de alquiler y no está en un modal
                        if ((wireModel && (wireModel.includes('alquiler_id') || wireModel === 'data.alquiler_id')) ||
                            (name && (name.includes('alquiler_id') || name === 'data.alquiler_id')) ||
                            (id && id.includes('alquiler'))) {
                            
                            // Verificar que no esté en un modal
                            if (!select.closest('.fi-modal')) {
                                alquilerSelect = select;
                                return;
                            }
                        }
                    });
                }

                if (alquilerSelect) {
                    
                    // Función para manejar el cambio de alquiler
                    function handleAlquilerChange(e) {
                        
                        // Actualizar la variable global
                        selectedAlquilerId = e.target.value;
                        
                        if (selectedAlquilerId) {
                            
                            // Hacer petición AJAX para obtener el resumen
                            fetch(`/admin/pagos-alquiler/get-resumen-data/${selectedAlquilerId}`)
                                .then(response => response.json())
                                .then(data => {
                                    
                                    // Actualizar variables globales
                                    pagosMensuales = data.pagosMensuales || [];
                                    detallesPagos = data.detallesPagos || [];
                                    totalAbonos = data.totalAbonos || 0;
                                    
                                    // Actualizar el footer con los nuevos datos
                                    updateFooterContent(data);
                                    showFooterIfHasData();

                                    // Usar el evento de Livewire en lugar de llamar directamente al método
                                    try {
                                        // Emitir evento que el componente Livewire está escuchando
                                        window.Livewire.emit('alquilerChanged', selectedAlquilerId);
                                    } catch (error) {
                                        console.error('Error al emitir evento alquilerChanged:', error);
                                        // Fallback: usar solo las variables globales
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al cargar resumen:', error);
                                });
                        } else {
                            // Limpiar datos cuando no hay alquiler seleccionado
                            pagosMensuales = [];
                            detallesPagos = [];
                            totalAbonos = 0;
                            showFooterIfHasData();
                        }
                    }

                    // Agregar event listeners
                    alquilerSelect.addEventListener('change', handleAlquilerChange);
                    alquilerSelect.addEventListener('input', handleAlquilerChange);
                    
                } else {
                    // No se encontró el select
                }
            }

            // Configurar cuando Livewire esté listo
            if (typeof window.Livewire !== 'undefined') {
                setupAlquilerListener();
            } else {
                document.addEventListener('livewire:load', function() {
                    setupAlquilerListener();
                });
            }

            // También configurar cuando Livewire se actualice
            document.addEventListener('livewire:update', function() {
                setupAlquilerListener();
            });

            // Configurar inicialmente
            setupAlquilerListener();
        });
    </script>
</x-filament::page>
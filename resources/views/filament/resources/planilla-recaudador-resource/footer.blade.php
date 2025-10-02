<div class="mt-8 space-y-6">
    <!-- TABLA DE PRÉSTAMOS REGULARES -->
    @if($estadoCredito !== 'adicionales')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="bg-yellow-400 px-4 py-2">
            <h2 class="text-center font-bold text-black text-lg">PLANILLA RECAUDADOR</h2>
        </div>
        
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">PRÉSTAMOS</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">N°</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Fecha</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">U. Abono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cliente</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Teléfono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Crédito</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Abonos</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Saldo</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cuota</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Atraso (Días)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Renovar</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($creditosRegulares as $index => $credito)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} dark:{{ $index % 2 == 0 ? 'bg-gray-800' : 'bg-gray-700' }}">
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->fecha_credito ? $credito->fecha_credito->format('d-M-y') : '-' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->ultima_fecha_pago ? \Carbon\Carbon::parse($credito->ultima_fecha_pago)->format('d-M-y') : 'Nunca' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->cliente_completo }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->telefono }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_credito, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->total_abonos, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->saldo_actual, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_cuota, 2) }}</td>
                        <td class="px-2 py-2 text-sm border border-gray-300 text-center {{ $credito->dias_atraso > 0 ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-gray-100' }}">{{ $credito->dias_atraso > 0 ? $credito->dias_atraso : 0 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-center">
                            @php
                                $creditoModel = \App\Models\Creditos::find($credito->id_credito);
                                $diasAtraso = $credito->dias_atraso;
                                
                                // Verificar si la configuración está habilitada solo para deshabilitar
                                try {
                                    $settings = app(\App\Settings\GeneralSettings::class);
                                    $configEnabled = $settings->enable_renovacion_filter ?? false;
                                } catch (\Exception $e) {
                                    $configEnabled = false;
                                }
                            @endphp
                            @if($credito->saldo_actual > 0 && $diasAtraso > 0)
                                @if($creditoModel && !$creditoModel->por_renovar)
                                    <button onclick="habilitarRenovacion({{ $credito->id_credito }}, '{{ $credito->cliente_completo }}', {{ $diasAtraso }}, {{ $credito->saldo_actual }})" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        Habilitar
                                    </button>
                                @elseif($creditoModel && $creditoModel->por_renovar && $configEnabled)
                                    <button onclick="deshabilitarRenovacion({{ $credito->id_credito }}, '{{ $credito->cliente_completo }}', {{ $diasAtraso }}, {{ $credito->saldo_actual }})" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        Deshabilitar
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Fila de totales para préstamos regulares -->
                    <tr class="bg-yellow-100 dark:bg-yellow-900 font-bold">
                        <td colspan="5" class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">TOTALES:</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['credito'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['abonos'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['saldo'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['cuota'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    <!-- TABLA DE ADICIONALES -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">ADICIONALES</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">N°</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Fecha</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">U. Abono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cliente</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Teléfono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Crédito</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Abonos</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Saldo</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cuota</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Atraso (Días)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Renovar</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($creditosAdicionales as $index => $credito)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} dark:{{ $index % 2 == 0 ? 'bg-gray-800' : 'bg-gray-700' }}">
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->fecha_credito ? $credito->fecha_credito->format('d-M-y') : '-' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->ultima_fecha_pago ? \Carbon\Carbon::parse($credito->ultima_fecha_pago)->format('d-M-y') : 'Nunca' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->cliente_completo }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->telefono }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_credito, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->total_abonos, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->saldo_actual, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->porcentaje_interes, 2) }}</td>
                        <td class="px-2 py-2 text-sm border border-gray-300 text-center {{ $credito->dias_atraso > 0 ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-gray-100' }}">{{ $credito->dias_atraso > 0 ? $credito->dias_atraso : 0 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-center">
                            @php
                                $creditoModel = \App\Models\Creditos::find($credito->id_credito);
                                $diasAtraso = $credito->dias_atraso;
                                
                                // Verificar si la configuración está habilitada solo para deshabilitar
                                try {
                                    $settings = app(\App\Settings\GeneralSettings::class);
                                    $configEnabled = $settings->enable_renovacion_filter ?? false;
                                } catch (\Exception $e) {
                                    $configEnabled = false;
                                }
                            @endphp
                            @if($credito->saldo_actual > 0 && $diasAtraso > 0)
                                @if($creditoModel && !$creditoModel->por_renovar)
                                    <button onclick="habilitarRenovacion({{ $credito->id_credito }}, '{{ $credito->cliente_completo }}', {{ $diasAtraso }}, {{ $credito->saldo_actual }})" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        Habilitar
                                    </button>
                                @elseif($creditoModel && $creditoModel->por_renovar && $configEnabled)
                                    <button onclick="deshabilitarRenovacion({{ $credito->id_credito }}, '{{ $credito->cliente_completo }}', {{ $diasAtraso }}, {{ $credito->saldo_actual }})" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        Deshabilitar
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Fila de totales para adicionales -->
                    <tr class="bg-yellow-100 dark:bg-yellow-900 font-bold">
                        <td colspan="5" class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">TOTALES:</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['credito'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['abonos'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['saldo'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['cuota'] ?? 0, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- TOTAL GENERAL -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="bg-green-100 dark:bg-green-900 p-4 rounded border-2 border-green-400">
            <h3 class="text-lg font-bold text-center text-gray-800 dark:text-gray-200 mb-2">TOTAL GENERAL</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Crédito Total</span>
                    <span class="block text-lg font-bold text-gray-900 dark:text-gray-100">S/ {{ number_format($totalGeneral['credito'], 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Abonos Total</span>
                    <span class="block text-lg font-bold text-green-600 dark:text-green-400">S/ {{ number_format($totalGeneral['abonos'], 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Total</span>
                    <span class="block text-lg font-bold text-red-600 dark:text-red-400">S/ {{ number_format($totalGeneral['saldo'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function habilitarRenovacion(creditoId, clienteNombre, diasAtraso, saldo) {
    // Crear modal de confirmación personalizado
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar Habilitación de Renovación</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        ¿Está seguro que desea habilitar este crédito para renovación?<br><br>
                        <strong>Cliente:</strong> ${clienteNombre}<br>
                        <strong>Días vencidos:</strong> ${diasAtraso}<br>
                        <strong>Saldo:</strong> S/ ${saldo.toFixed(2)}
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmar-habilitar" class="px-4 py-2 bg-yellow-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                        Sí
                    </button>
                    <button id="cancelar-habilitar" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        No
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar eventos del modal
    document.getElementById('confirmar-habilitar').onclick = function() {
        document.body.removeChild(modal);
        
        // Crear formulario para enviar la acción
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/planilla-recaudador/renovacion';
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Acción
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'habilitar_renovacion';
        form.appendChild(actionInput);
        
        // ID del crédito
        const creditoInput = document.createElement('input');
        creditoInput.type = 'hidden';
        creditoInput.name = 'credito_id';
        creditoInput.value = creditoId;
        form.appendChild(creditoInput);
        
        document.body.appendChild(form);
        form.submit();
    };
    
    document.getElementById('cancelar-habilitar').onclick = function() {
        document.body.removeChild(modal);
    };
    
    // Cerrar modal al hacer clic fuera
    modal.onclick = function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    };
}

function deshabilitarRenovacion(creditoId, clienteNombre, diasAtraso, saldo) {
    // Crear modal de confirmación personalizado
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar Deshabilitación de Renovación</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        ¿Está seguro que desea deshabilitar este crédito para renovación?<br><br>
                        <strong>Cliente:</strong> ${clienteNombre}<br>
                        <strong>Días vencidos:</strong> ${diasAtraso}<br>
                        <strong>Saldo:</strong> S/ ${saldo.toFixed(2)}
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmar-deshabilitar" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Sí
                    </button>
                    <button id="cancelar-deshabilitar" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        No
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar eventos del modal
    document.getElementById('confirmar-deshabilitar').onclick = function() {
        document.body.removeChild(modal);
        
        // Crear formulario para enviar la acción
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/planilla-recaudador/renovacion';
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Acción
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'deshabilitar_renovacion';
        form.appendChild(actionInput);
        
        // ID del crédito
        const creditoInput = document.createElement('input');
        creditoInput.type = 'hidden';
        creditoInput.name = 'credito_id';
        creditoInput.value = creditoId;
        form.appendChild(creditoInput);
        
        document.body.appendChild(form);
        form.submit();
    };
    
    document.getElementById('cancelar-deshabilitar').onclick = function() {
        document.body.removeChild(modal);
    };
    
    // Cerrar modal al hacer clic fuera
    modal.onclick = function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    };
}
</script>
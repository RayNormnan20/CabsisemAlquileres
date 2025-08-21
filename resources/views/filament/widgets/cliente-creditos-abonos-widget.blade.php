<x-filament::widget>
    <x-filament::card>
        @if(!$rutaSeleccionada)
            <div class="flex items-center justify-center p-4">
                <p class="text-gray-500">Seleccione una ruta para ver los créditos y abonos asociados.</p>
            </div>
        @else
            <div class="space-y-4">
            
                <!-- Información de la Ruta -->
                <div class="p-4 bg-white rounded-lg shadow">
                    <h2 class="text-xl font-bold text-primary-600 mb-2">Información de la Ruta</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="font-medium">Ruta: <span class="font-normal">{{ $ruta['nombre'] ?? 'No disponible' }}</span></p>
                            <p class="font-medium">Descripción: <span class="font-normal">{{ $ruta['descripcion'] ?? 'No disponible' }}</span></p>
                        </div>
                        <div>
                            <p class="font-medium">Usuarios asignados: <span class="font-normal">{{ count($usuarios) }}</span></p>
                            @if(count($usuarios) > 0)
                                <div class="mt-2">
                                    <p class="font-medium text-sm">Usuarios:</p>
                                    <ul class="text-sm text-gray-600 ml-4">
                                        @foreach($usuarios as $usuario)
                                            <li>• {{ $usuario['nombres'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Listado de Clientes 
                <div class="p-4 bg-white rounded-lg shadow">
                    <h2 class="text-xl font-bold text-primary-600 mb-4">Clientes Asociados ({{ count($clientes) }})</h2>
                    @if(count($clientes) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($clientes as $cliente)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente['nombre'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente['documento'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente['telefono'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cliente['estado'] === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cliente['estado'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-gray-500">No hay clientes asociados a esta ruta.</div>
                    @endif
                </div>
                -->
                
                <!-- Resto del código (Resumen financiero y pestañas) se mantiene igual -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900">Total Créditos</h3>
                        <p class="text-2xl font-bold text-primary-600">$ {{ number_format($totalCreditos, 2) }}</p>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900">Total Abonos</h3>
                        <p class="text-2xl font-bold text-success-600">$ {{ number_format($totalAbonos, 2) }}</p>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900">Saldo Pendiente</h3>
                        <p class="text-2xl font-bold {{ $saldoPendiente > 0 ? 'text-danger-600' : 'text-success-600' }}">$ {{ number_format($saldoPendiente, 2) }}</p>
                    </div>
                </div>
                
                <!-- Pestañas para Créditos y Abonos (actualizadas para mostrar cliente) -->
                <div x-data="{ activeTab: 'creditos' }" class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button 
                                @click="activeTab = 'creditos'" 
                                :class="{ 'border-primary-500 text-primary-600': activeTab === 'creditos', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'creditos' }"
                                class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                Créditos ({{ count($creditos) }})
                            </button>
                            <button 
                                @click="activeTab = 'abonos'" 
                                :class="{ 'border-primary-500 text-primary-600': activeTab === 'abonos', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'abonos' }"
                                class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                Abonos ({{ count($abonos) }})
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Tabla de Créditos (actualizada con cliente) -->
                    <div x-show="activeTab === 'creditos'" class="overflow-x-auto">
                        @if(count($creditos) > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conceptos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($creditos as $credito)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $credito['cliente_nombre'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $credito['fecha'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/. {{ number_format($credito['valor'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/. {{ number_format($credito['saldo'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $credito['saldo'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $credito['saldo'] > 0 ? 'Activo' : 'Pagado' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="space-y-1">
                                                    @foreach($credito['conceptos'] as $concepto)
                                                        <div class="flex justify-between">
                                                            <span>{{ $concepto['tipo'] }}:</span>
                                                            <span>S/. {{ number_format($concepto['monto'], 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-4 text-center text-gray-500">No hay créditos registrados.</div>
                        @endif
                    </div>
                    
                    <!-- Tabla de Abonos (actualizada con cliente) -->
                    <div x-show="activeTab === 'abonos'" class="overflow-x-auto">
                        @if(count($abonos) > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                      <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crédito ID</th> -->
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conceptos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($abonos as $abono)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $abono['cliente_nombre'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $abono['fecha'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/. {{ number_format($abono['monto'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $abono['credito_id'] }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="space-y-1">
                                                    @foreach($abono['conceptos'] as $concepto)
                                                        <div class="flex justify-between">
                                                            <span>{{ $concepto['tipo'] }}:</span>
                                                            <span>S/. {{ number_format($concepto['monto'], 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-4 text-center text-gray-500">No hay abonos registrados.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>
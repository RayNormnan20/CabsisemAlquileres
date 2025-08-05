<x-filament::page>
    <div class="space-y-6">
        <!-- Widget de estadísticas TEMPORALMENTE DESHABILITADO -->
        {{-- @livewire(\App\Filament\Widgets\TrasladosStatsWidget::class) --}}

        <!-- Información de ayuda -->
        <x-filament::card>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Información sobre el Traslado de Clientes
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Sólo Saldo:</strong> Traslada únicamente el cliente con su saldo pendiente actual.</li>
                                <li><strong>Historial Completo:</strong> Traslada el cliente con todo su historial de créditos y abonos.</li>
                                <li>El traslado es una operación irreversible, asegúrese de seleccionar las opciones correctas.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <!-- Formulario principal -->
        <x-filament::card>
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">Configuración del Traslado</h2>
                
                {{ $this->form }}
                
                @php
                    $clientesConDatos = $this->getClientesConDatosProperty();
                    $clientesSeleccionados = $this->getClientesSeleccionadosProperty();
                    $clientesDisponiblesCount = count($clientesConDatos);
                    $clientesSeleccionadosCount = count($clientesSeleccionados);
                @endphp

                @if($this->getRutaOrigenProperty() && $clientesDisponiblesCount === 0)
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-700">
                            No hay clientes activos en la ruta seleccionada.
                        </p>
                    </div>
                @endif
            </div>
        </x-filament::card>

        <!-- Tabla de Clientes -->
        @if($clientesDisponiblesCount > 0)
            <x-filament::card>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                                               wire:click="$toggle('selectAll')"
                                               @if(count($clientesSeleccionados) === count($clientesConDatos)) checked @endif>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha de Crédito
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Vencimiento
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Próximo Pago
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Saldo
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($clientesConDatos as $cliente)
                                    <tr class="hover:bg-gray-50 {{ in_array($cliente['id_cliente'], $clientesSeleccionados) ? 'bg-blue-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" 
                                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                                                   wire:click="toggleClienteSeleccion({{ $cliente['id_cliente'] }})"
                                                   @if(in_array($cliente['id_cliente'], $clientesSeleccionados)) checked @endif>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $cliente['nombre'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $cliente['fecha_credito'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $cliente['fecha_vencimiento'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $cliente['fecha_proximo_pago'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $cliente['saldo'] }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::card>
        @endif

        <!-- Botones de acción -->
        @if($clientesDisponiblesCount > 0)
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    @if($clientesSeleccionadosCount > 0)
                        {{ $clientesSeleccionadosCount }} cliente(s) seleccionado(s)
                    @else
                        Seleccione los clientes que desea trasladar
                    @endif
                </div>
                
                <div class="flex space-x-3">
                    <x-filament::button
                        wire:click="trasladar"
                        color="primary"
                        :disabled="empty($clientesSeleccionados) || !$this->getRutaDestinoProperty()"
                    >
                        Procesar Traslado
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
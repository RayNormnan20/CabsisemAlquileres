<div>
    <div class="bg-white rounded-lg shadow-lg max-w-2xl mx-auto">
        <!-- Header del Modal -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Comprobantes de Pago</h2>
        </div>

        <!-- Contenido del Modal -->
        <div class="p-6">
            @if($credito && $comprobantes->count() > 0)
                <div class="space-y-4">
                    @foreach($comprobantes as $concepto)
                        @if($concepto->foto_comprobante)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-gray-700 font-medium">
                                            {{ $concepto->concepto->nombre ?? 'Comprobante Efectivo' }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Monto: S/ {{ number_format($concepto->monto, 2) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Botón de descarga -->
                                    <div class="ml-4">
                                        <a href="{{ asset('storage/' . $concepto->foto_comprobante) }}" 
                                           download="{{ $concepto->concepto->nombre ?? 'Comprobante' }}_{{ $credito->id_credito }}.{{ pathinfo($concepto->foto_comprobante, PATHINFO_EXTENSION) }}"
                                           class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Descargar
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Vista previa del archivo (opcional) -->
                                @php
                                    $extension = pathinfo($concepto->foto_comprobante, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp
                                
                                @if($isImage)
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/' . $concepto->foto_comprobante) }}" 
                                             alt="Vista previa del comprobante" 
                                             class="max-w-full h-32 object-contain rounded border cursor-pointer hover:opacity-75"
                                             onclick="window.open('{{ asset('storage/' . $concepto->foto_comprobante) }}', '_blank')">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500">No hay comprobantes disponibles para este crédito.</p>
                </div>
            @endif
        </div>

        <!-- Footer del Modal -->
        <div class="flex justify-end p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <button wire:click="$emit('closeModal')" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 mr-2">
                Cancelar
            </button>
            <button wire:click="$emit('closeModal')" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Cerrar
            </button>
        </div>
    </div>
</div>
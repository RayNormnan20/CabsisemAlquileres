<x-filament::page>
    <!-- Botón Atrás -->
    <div class="mb-4 flex justify-end">
        <a href="{{ route('filament.pages.dashboard') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Atrás
        </a>
    </div>
    
    <div class="w-full space-y-6">
        <!-- Encabezado con totales -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">📋 Lista Detallada de Pagos del Día</h2>
                <div class="text-sm text-gray-600">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">💳 YAPE</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $countYape }} pagos</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-yellow-600">S/ {{ number_format($totalYape, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-emerald-600">💵 EFECTIVO</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $countEfectivo }} pagos</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-emerald-600">S/ {{ number_format($totalEfectivo, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">📊 TOTAL GENERAL</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $countGeneral }} pagos</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($totalGeneral, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor con pestañas -->
        <div x-data="{ activeTab: 'yape' }" class="w-full">
            <!-- Pestañas -->
            <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                <button 
                    @click="activeTab = 'yape'"
                    :class="activeTab === 'yape' ? 'bg-yellow-500 text-white shadow-md' : 'text-gray-600 hover:text-gray-800'"
                    class="flex-1 py-3 px-6 rounded-md text-sm font-medium transition-all duration-200">
                    💳 PAGOS YAPE ({{ $countYape }})
                </button>
                <button 
                    @click="activeTab = 'efectivo'"
                    :class="activeTab === 'efectivo' ? 'bg-emerald-500 text-white shadow-md' : 'text-gray-600 hover:text-gray-800'"
                    class="flex-1 py-3 px-6 rounded-md text-sm font-medium transition-all duration-200">
                    💵 PAGOS EFECTIVO ({{ $countEfectivo }})
                </button>
            </div>

            <!-- Contenido de YAPE -->
            <div x-show="activeTab === 'yape'" class="space-y-4">
                @if($pagosYapeHoy->count() > 0)
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <h3 class="text-lg font-semibold text-yellow-700 mb-4">💳 Pagos YAPE del día ({{ $countYape }} pagos - S/ {{ number_format($totalYape, 2) }})</h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($pagosYapeHoy as $pago)
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-yellow-100 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                    💳 YAPE
                                                </span>
                                                <span class="text-lg font-semibold text-gray-800">
                                                    {{ $pago->credito->cliente->nombre_completo ?? 'Cliente no encontrado' }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1 ml-3">
                                                <p><strong>Cobrador:</strong> {{ $pago->usuario->name ?? 'No asignado' }}</p>
                                                <p><strong>Hora:</strong> {{ $pago->created_at->format('H:i:s') }}</p>
                                                <p><strong>Fecha completa:</strong> {{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
                                                @if($pago->observaciones)
                                                    <p><strong>Observaciones:</strong> {{ $pago->observaciones }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-yellow-600">S/ {{ number_format($pago->monto_abono, 2) }}</p>
                                            <p class="text-sm text-gray-500">{{ $pago->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 p-8 rounded-lg border border-yellow-200 text-center">
                        <div class="text-yellow-400 text-6xl mb-4">💳</div>
                        <p class="text-yellow-700 font-medium text-lg">No hay pagos YAPE registrados hoy</p>
                        <p class="text-yellow-600 text-sm mt-2">Los pagos aparecerán aquí cuando se registren</p>
                    </div>
                @endif
            </div>

            <!-- Contenido de EFECTIVO -->
            <div x-show="activeTab === 'efectivo'" class="space-y-4">
                @if($pagosEfectivoHoy->count() > 0)
                    <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                        <h3 class="text-lg font-semibold text-emerald-700 mb-4">💵 Pagos EFECTIVO del día ({{ $countEfectivo }} pagos - S/ {{ number_format($totalEfectivo, 2) }})</h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($pagosEfectivoHoy as $pago)
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-100 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                                    💵 EFECTIVO
                                                </span>
                                                <span class="text-lg font-semibold text-gray-800">
                                                    {{ $pago->credito->cliente->nombre_completo ?? 'Cliente no encontrado' }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1 ml-3">
                                                <p><strong>Cobrador:</strong> {{ $pago->usuario->name ?? 'No asignado' }}</p>
                                                <p><strong>Hora:</strong> {{ $pago->created_at->format('H:i:s') }}</p>
                                                <p><strong>Fecha completa:</strong> {{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
                                                @if($pago->observaciones)
                                                    <p><strong>Observaciones:</strong> {{ $pago->observaciones }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-emerald-600">S/ {{ number_format($pago->monto_abono, 2) }}</p>
                                            <p class="text-sm text-gray-500">{{ $pago->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 p-8 rounded-lg border border-emerald-200 text-center">
                        <div class="text-emerald-400 text-6xl mb-4">💵</div>
                        <p class="text-emerald-700 font-medium text-lg">No hay pagos en EFECTIVO registrados hoy</p>
                        <p class="text-emerald-600 text-sm mt-2">Los pagos aparecerán aquí cuando se registren</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
<x-filament::card>
    <!-- Contenedor principal que ocupa todo el ancho -->
    <div class="w-full space-y-6">

        <!-- Primera fila: Cards con valores principales -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">POR COBRAR</h3>
                <p class="text-2xl font-bold text-red-500">
                    S/ {{ number_format($porCobrar, 2) }}
                </p>
                <p class="text-xs text-gray-500">Total vencido + hoy</p>
            </div>

            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">PAGOS HOY</h3>
                <p class="text-2xl font-bold text-green-500">
                    S/ {{ number_format($pagosHoy, 2) }}
                </p>
                <p class="text-xs text-gray-500">Total recaudado hoy</p>
            </div>

            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Cuotas Hoy</h3>
                <p class="text-2xl font-bold text-orange-500">
                    {{ $cuotasHoy }}
                </p>
                <p class="text-xs text-gray-500">Vencen hoy</p>
            </div>
            
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Pagadas Hoy</h3>
                <p class="text-2xl font-bold text-blue-500">
                    {{ $pagadasHoy }}
                </p>
                <p class="text-xs text-gray-500">Registrados hoy</p>
            </div>
        </div>

        <!-- Segunda fila: Secciones adicionales con conteos y sumas detalladas -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 w-full">
            <!-- CLIENTES (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-primary-500"
                onclick="window.location.href='{{ route('filament.resources.cliente-alquilers.index') }}'">
                <div class="text-center">
                    <div class="bg-primary-100 p-1 rounded-full w-10 h-10 items-center justify-center mx-auto mb-1 hidden md:flex">
                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">CLIENTES</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $clientesCount }} registrados</p>
                </div>
            </div>

            <!-- ALQUILERES ACTIVOS -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-indigo-500"
                onclick="window.location.href='{{ route('filament.resources.alquilers.index') }}'">
                <div class="text-center">
                    <div class="bg-indigo-100 p-1 rounded-full w-10 h-10 items-center justify-center mx-auto mb-1 hidden md:flex">
                        <x-heroicon-o-office-building class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">ALQUILERES</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $alquileresActivosCount }} activos</p>
                    <p class="text-[10px] text-gray-500 leading-tight">S/ {{ number_format($alquileresActivosMonto, 2) }} mensual</p>
                </div>
            </div>

            <!-- PAGOS DETALLE -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-blue-500">
                <div class="text-center">
                    <div class="bg-blue-100 p-1 rounded-full w-10 h-10 items-center justify-center mx-auto mb-1 hidden md:flex">
                        <x-heroicon-o-cash class="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">PAGOS</h3>
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] text-yellow-600 font-medium">DIGITAL:</span>
                            <span class="text-[9px] text-gray-700"><span class="font-bold px-1">{{ $countPagosDigitalHoy }}</span> (S/ {{ number_format($totalPagosDigitalHoy, 2) }})</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] text-emerald-600 font-medium">EFECTIVO:</span>
                            <span class="text-[9px] text-gray-700"><span class="font-bold px-1">{{ $countPagosEfectivoHoy }}</span> (S/ {{ number_format($totalPagosEfectivoHoy, 2) }})</span>
                        </div>
                        <div class="border-t pt-1">
                            <p class="text-[10px] text-gray-800 font-bold">Total: S/ {{ number_format($pagosHoy, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament::card>

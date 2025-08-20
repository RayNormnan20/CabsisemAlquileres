<x-filament::card>
    <!-- Contenedor principal que ocupa todo el ancho -->
    <div class="w-full space-y-6">

        <!-- Primera fila: Cards con valores principales (CUA, Clientes de la Ruta, Total Créditos) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Card CUA Actual 
             
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">TOTAL CRÉDITOS OTORGADOS</h3>
                <p class="text-2xl font-bold text-purple-500">
                     {{ $totalCreditosRutaCount }}
                </p>
            </div>-->

            <!-- Card CUA Anterior -->

            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">POR COBRAR</h3>
                <p class="text-2xl font-bold text-purple-500">
                    S/ {{ $totalCuotasHoySum }}
                </p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">PAGOS HOY</h3>
                <p class="text-2xl font-bold text-purple-500">
                    S/ {{ $totalAbonosHoySum }}
                </p>
            </div>

            <!-- Card Clientes en Ruta Actual 
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">CLIENTES EN RUTA</h3>
                <p class="text-2xl font-bold text-blue-500">
                    {{ $totalClientesRuta }} {{-- ¡CORREGIDO! Usar la variable filtrada por ruta --}}
                </p>
            </div>-->

            <!-- Card Total de Créditos Otorgados en Ruta 
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">CRÉDITOS OTORGADOS</h3>
                <p class="text-2xl font-bold text-purple-500">
                    S/ {{ $totalCreditosRutaSum }}
                </p>
            </div>-->

            <!--
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Cuotas Pendientes</h3>
                <p class="text-2xl font-bold text-purple-500">
                     {{ $cuotasPendientes }}
                </p>
            </div>
        
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Cuotas Vencidas</h3>
                <p class="text-2xl font-bold text-purple-500">
                     {{ $cuotasVencidas }}
                </p>
            </div>
            -->
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Cuotas Hoy</h3>
                <p class="text-2xl font-bold text-purple-500">
                    {{ $cuotasHoy }}
                </p>
            </div>
            
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">Pagadas Hoy</h3>
                <p class="text-2xl font-bold text-green-500">
                    {{ $cuotasPagadasHoy }}
                </p>
            </div>
        </div>

        <!-- Tercera fila: Secciones adicionales con conteos y sumas detalladas -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 w-full">
            <!-- CLIENTES (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-primary-500"
                onclick="window.location.href='{{ route('filament.resources.clientes.index') }}'">
                <div class="text-center">
                    <div class="bg-primary-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">CLIENTES</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $totalClientesRuta }} registrados</p> {{-- ¡CORREGIDO! --}}
                </div>
            </div>

            <!-- ABONOS (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-green-500"
                onclick="window.location.href='{{ route('filament.resources.abonos.index') }}'">
                <div class="text-center">
                    <div class="bg-green-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-cash class="w-5 h-5 text-green-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">ABONOS</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $totalAbonosRutaCount }} registrados</p>
                    <p class="text-[10px] text-gray-500 leading-tight">S/ {{ $totalAbonosRutaSum }} abonados</p>
                </div>
            </div>

            <!-- CRÉDITOS (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-indigo-500"
                onclick="window.location.href='{{ route('filament.resources.creditos.index') }}'">
                <div class="text-center">
                    <div class="bg-indigo-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-office-building class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">CRÉDITOS</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $totalCreditosRutaCount }} activos</p> {{-- ¡CORREGIDO! --}}
                    <p class="text-[10px] text-gray-500 leading-tight">S/ {{ $totalCreditosRutaSum }} otorgados</p>
                </div>
            </div>

            <!-- PAGOS YAPE (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-yellow-500"
                onclick="window.location.href='{{ route('filament.resources.abonos.index') }}'">
                <div class="text-center">
                    <div class="bg-yellow-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-eye class="w-5 h-5 text-yellow-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">PAGOS YAPE</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $countPagosYapeHoy }} pagos hoy</p>
                    <p class="text-[10px] text-gray-500 leading-tight">S/ {{ $totalPagosYapeHoy }} total</p>
                </div>
            </div>

            <!-- PAGOS EFECTIVO (detalle) -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-emerald-500"
                onclick="window.location.href='{{ route('filament.resources.abonos.index') }}'">
                <div class="text-center">
                    <div class="bg-emerald-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-eye class="w-5 h-5 text-emerald-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">PAGOS EFECTIVO</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ $countPagosEfectivoHoy }} pagos hoy</p>
                    <p class="text-[10px] text-gray-500 leading-tight">S/ {{ $totalPagosEfectivoHoy }} total</p>
                </div>
            </div>
        </div>

    </div>
</x-filament::card>

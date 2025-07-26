<x-filament::card>
    <!-- Contenedor principal que ocupa todo el ancho -->
    <div class="w-full space-y-6">

        <!-- Primera fila: Cards con valores -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Card CUA Actual -->
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">CUA ACTUAL</h3>
                <p class="text-2xl font-bold {{ $cuaActual < 0 ? 'text-red-500' : 'text-green-500' }}">
                    {{ $cuaActual }}
                </p>
            </div>

            <!-- Card CUA Anterior -->
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">CUA ANTERIOR</h3>
                <p class="text-2xl font-bold {{ $cuaAnterior < 0 ? 'text-red-500' : 'text-green-500' }}">
                    {{ $cuaAnterior }}
                </p>
            </div>

            <!-- Card Clientes PARADIGM -->
            <div class="p-4 bg-white rounded-lg shadow w-full">
                <h3 class="text-lg font-semibold text-gray-700">CLIENTES</h3>
                <p class="text-2xl font-bold text-blue-500">
                    {{ $totalClientes }}
                </p>
            </div>
        </div>

        <!-- Segunda fila: Barra de ingresos/gastos
        <div class="w-full bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">INGRESOS Y GASTOS</h3>
            <div class="grid grid-cols-5 gap-4 w-full">
                @foreach($ingresosGastos as $monto)
                <div class="bg-primary-500 text-white p-3 rounded-lg text-center">
                    <span class="font-bold text-lg">{{ $monto }}</span>
                </div>
                @endforeach
            </div>
        </div>
-->
            <!-- Tercera fila: Secciones adicionales -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 w-full">
            <!-- YAPE CLIENTES -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-primary-500"
                onclick="window.location.href='{{ route('filament.resources.clientes.index') }}'">
                <div class="text-center">
                    <div class="bg-primary-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">CLIENTES</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ App\Models\Clientes::count() }} registrados</p>
                </div>
            </div>

            <!-- ABONOS -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-green-500"
                onclick="window.location.href='{{ route('filament.resources.abonos.index') }}'">
                <div class="text-center">
                    <div class="bg-green-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-cash class="w-5 h-5 text-green-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">ABONOS</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">Últimos pagos</p>
                </div>
            </div>

            <!-- CRÉDITOS -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-indigo-500"
                onclick="window.location.href='{{ route('filament.resources.creditos.index') }}'">
                <div class="text-center">
                    <div class="bg-indigo-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-office-building class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">CRÉDITOS</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ App\Models\Creditos::count() }} activos</p>
                    </div>
            </div>

            <!-- YAPE CLIENTES duplicado -->
            <div class="bg-white p-2 rounded-xl shadow cursor-pointer transition hover:scale-105 hover:shadow-xl border-l-4 border-primary-500"
                onclick="window.location.href='{{ route('filament.resources.clientes.index') }}'">
                <div class="text-center">
                    <div class="bg-primary-100 p-1 rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">
                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                    </div>
                    <h3 class="text-xs font-bold text-gray-800 leading-tight">CLIENTES</h3>
                    <p class="text-[10px] text-gray-500 leading-tight">{{ App\Models\Clientes::count() }} registrados</p>
                </div>
            </div>
        </div>

    </div>
</x-filament::card>

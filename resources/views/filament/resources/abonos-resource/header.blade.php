<div class="flex flex-col space-y-4">
    <!-- Fila superior con selectores, filtros y botón -->
    <div class="flex items-center justify-between gap-4">
        <!-- Selector de cliente -->
        <div class="flex-1">
            <select wire:model="clienteId"
                wire:change="$set('clienteId', $event.target.value === '' ? null : parseInt($event.target.value))"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Todos los clientes</option> <!-- Cambiado el texto -->
                @foreach($clientes as $id => $nombre)
                <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        @role('Administrador')
        <div class="flex-6">
            <select wire:model="rutaId"
                wire:change="$set('rutaId', $event.target.value === '' ? null : parseInt($event.target.value))"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $id => $nombre)
                <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        @endrole

        <!-- Contenedor para filtro y botón - SIEMPRE visible -->
        <div class="flex items-center space-x-">
            <!-- Componente unificado de filtro de fechas -->
            <div class="flex items-center bg-white rounded-md border border-gray-300 overflow-hidden">
                <!-- Selector de período -->
                <select wire:model="periodoSeleccionado" wire:change="aplicarPeriodo"
                    class="border-none focus:ring-0 py-1 pl-2 pr-6 text-sm bg-gray-50">
                    <option value="hoy">Hoy</option> <!-- Opción por defecto -->
                    <option value="ayer">Ayer</option>
                    <option value="semana_actual">Esta semana</option>
                    <option value="semana_anterior">Semana pasada</option>
                    <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                    <option value="mes_actual">Este mes</option>
                    <option value="mes_anterior">Mes pasado</option>
                    <option value="personalizado">Personalizado</option>
                </select>

                <!-- Divisor visual -->
                <div class="h-6 w-px bg-gray-300"></div>

                <!-- Inputs de fecha integrados -->
                <div class="flex items-center px-2">
                    <input type="date" wire:model="fechaDesde" class="border-none focus:ring-0 p-0 text-sm w-28"
                        placeholder="Desde">
                    <span class="text-gray-500 mx-1">-</span>
                    <input type="date" wire:model="fechaHasta" class="border-none focus:ring-0 p-0 text-sm w-28"
                        placeholder="Hasta">
                </div>

                <!-- Botón para limpiar -->
                @if($this->fechaDesde || $this->fechaHasta)
                <button wire:click="resetFechas" class="px-2 text-gray-400 hover:text-red-500 h-full flex items-center">
                    ×
                </button>
                @endif
            </div>
            <!-- Botón Crear Abono -->
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 {{ !$clienteId ? 'opacity-50 cursor-not-allowed' : '' }}"
                    :class="{ 'bg-primary-700': open }" :disabled="!$clienteId">
                    Acciones
                    <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Menú desplegable simplificado -->
                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                    role="menu">
                    <div class="py-1" role="none">
                        <!-- Opción Yape con parámetro en la URL -->
                        <a href="{{ $clienteId ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Yape']) : '#' }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ !$clienteId ? 'opacity-50 cursor-not-allowed' : '' }}"
                            wire:navigate @if(!$clienteId) onclick="return false;" @endif>
                            Yape
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Opción Efectivo -->
                        <a href="{{ $clienteId ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Efectivo']) : '#' }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ !$clienteId ? 'opacity-50 cursor-not-allowed' : '' }}"
                            wire:navigate @if(!$clienteId) onclick="return false;" @endif>
                            Efectivo
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Única opción: Crear Crédito -->
                        <a href="{{ $clienteId ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Abono completar p.']) : '#' }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ !$clienteId ? 'opacity-50 cursor-not-allowed' : '' }}"
                            wire:navigate @if(!$clienteId) onclick="return false;" @endif>
                            Abono completar p.
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Única opción: Crear Crédito -->
                        <a href="{{ $clienteId ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Abono sin firma Chis']) : '#' }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ !$clienteId ? 'opacity-50 cursor-not-allowed' : '' }}"
                            wire:navigate @if(!$clienteId) onclick="return false;" @endif>
                            Abono sin firma Chis
                        </a>
                    </div>
                    <!-- Ejemplo para "otros egresos" -->
                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'OTROS EGRESOS']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            Otros egresos
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'OTROS INGRESOS']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            Otros ingresos
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO SOBRANTE COB']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            Abono sobrante cob
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO FALTANTE COB']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            Abono faltante cob
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ENTREGA CAJA COBRADOR']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            Entrega caja COBRADOR
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO DE DESCUENTO']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            ABONO DE DESCUENTO
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- Información del cliente seleccionado -->
    @if($clienteId)
    @php
    $cliente = \App\Models\Clientes::withCount(['creditos', 'abonos'])->find($clienteId);
    @endphp
    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $cliente->nombre_completo }}</h3>
                <p class="text-sm text-gray-600">DNI: {{ $cliente->numero_documento }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm">
                    <span class="font-medium">Créditos:</span> {{ $cliente->creditos_count }}
                </p>
                <p class="text-sm">
                    <span class="font-medium">Abonos:</span> {{ $cliente->abonos_count }}
                </p>
                <p class="text-sm">
                    <span class="font-medium">Estado:</span>
                    <span class="{{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">
                        {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
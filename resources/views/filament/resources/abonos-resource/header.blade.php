<div class="flex flex-col space-y-4 bg-white dark:bg-gray-800 p-4 rounded-lg">
    <!-- Layout responsive: una fila en desktop, dos filas en móvil -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Primera fila en móvil: selectores -->
        <div class="flex flex-col sm:flex-row gap-4 lg:flex-1">
            <!-- Selector de cliente searchable -->
            <div class="flex-1 relative" x-data="{
            open: false,
            search: '',
            selectedClienteId: @entangle('clienteId'),
            get filteredClientes() {
                if (!this.search) return @js($clientes);
                const clientes = @js($clientes);
                const filtered = {};
                Object.keys(clientes).forEach(id => {
                    if (clientes[id].toLowerCase().includes(this.search.toLowerCase())) {
                        filtered[id] = clientes[id];
                    }
                });
                return filtered;
            },
            get selectedClienteName() {
                if (!this.selectedClienteId) return 'Todos los clientes';
                const clientes = @js($clientes);
                return clientes[this.selectedClienteId] || 'Cliente no encontrado';
            },
            selectCliente(id, nombre) {
                this.selectedClienteId = id === '' ? null : parseInt(id);
                this.open = false;
                this.search = '';
            }
        }">
                <!-- Input/Button principal -->
                <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-left focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span x-text="selectedClienteName" class="block truncate"></span>
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.away="open = false"
                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-hidden">
                    <!-- Input de búsqueda -->
                    <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                        <input x-model="search" type="text" placeholder="Buscar cliente..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Lista de opciones -->
                    <div class="max-h-48 overflow-y-auto">
                        <!-- Opción "Todos los clientes" -->
                        <button @click="selectCliente('', 'Todos los clientes')" type="button"
                            class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                            :class="{ 'bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400': selectedClienteId === null }">
                            Todos los clientes
                        </button>

                        <!-- Opciones de clientes filtradas -->
                        <template x-for="[id, nombre] in Object.entries(filteredClientes)" :key="id">
                            <button @click="selectCliente(id, nombre)" type="button"
                                class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                                :class="{ 'bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400': selectedClienteId == id }">
                                <span x-text="nombre"></span>
                            </button>
                        </template>

                        <!-- Mensaje cuando no hay resultados -->
                        <div x-show="Object.keys(filteredClientes).length === 0 && search !== ''"
                            class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm">
                            No se encontraron clientes
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1">
                <select wire:model="tipoConcepto"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos los métodos</option>
                    <option value="Yape">Yape</option>
                    <option value="Efectivo">Efectivo</option>
                </select>
            </div>
            <!--
            @role('Administrador')
            <div class="flex-6">
                <select wire:model="rutaId"
                    wire:change="$set('rutaId', $event.target.value === '' ? null : parseInt($event.target.value))"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todas las rutas</option>
                    @foreach($rutas as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endrole
        -->
        </div>

        <!-- Segunda fila en móvil: filtros de fecha y botón de abonos -->
        <div class="flex flex-col sm:flex-row items-center gap-4 lg:gap-5">
            <!-- Componente unificado de filtro de fechas -->
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <!-- Botón desplegable -->
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-calendar class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                    {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d M Y') : 'Desde' }}
                    -
                    {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d M Y') : 'Hasta' }}
                    <svg class="w-4 h-4 ml-1 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.away="open = false"
                    class="absolute z-50 mt-2 w-90 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 p-4 space-y-3">
                    <!-- Selector de período -->
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Período:</label>
                        <select wire:model="periodoSeleccionado" wire:change="aplicarPeriodo"
                            class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="hoy">Hoy</option>
                            <option value="ayer">Ayer</option>
                            <option value="semana_actual">Esta semana</option>
                            <option value="semana_anterior">Semana pasada</option>
                            <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                            <option value="mes_actual">Este mes</option>
                            <option value="mes_anterior">Mes pasado</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    <!-- Rango de fechas -->
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Rango personalizado:</label>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model="fechaDesde"
                                class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm" />
                            <span class="text-gray-500 dark:text-gray-400">-</span>
                            <input type="date" wire:model="fechaHasta"
                                class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- Botón Crear Abono -->
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150"
                :class="{ 'bg-primary-700': open }">
                    Abonos
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
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none z-10"
                    role="menu">
                    <div class="py-1" role="none">
                        <!-- Opción Yape con parámetro en la URL -->
                        <a href="{{ ($clienteId ?? null) ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Yape']) : '#' }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 {{ !($clienteId ?? null) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        wire:navigate @if(!($clienteId ?? null)) onclick="return false;" @endif>
                            Yape
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Opción Efectivo -->
                        <a href="{{ ($clienteId ?? null) ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Efectivo']) : '#' }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 {{ !($clienteId ?? null) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        wire:navigate @if(!($clienteId ?? null)) onclick="return false;" @endif>
                            Efectivo
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Única opción: Crear Crédito -->
                        <a href="{{ ($clienteId ?? null) ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Abono completar p.']) : '#' }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 {{ !($clienteId ?? null) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        wire:navigate @if(!($clienteId ?? null)) onclick="return false;" @endif>
                            Abono completar p.
                        </a>
                    </div>
                    <div class="py-1" role="none">
                        <!-- Única opción: Crear Crédito -->
                        <a href="{{ ($clienteId ?? null) ? route('filament.resources.abonos.create', ['cliente_id' => $clienteId, 'metodo_pago' => 'Abono sin firma Chis']) : '#' }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 {{ !($clienteId ?? null) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        wire:navigate @if(!($clienteId ?? null)) onclick="return false;" @endif>
                            Abono sin firma Chis
                        </a>
                    </div>
                    <!-- Ejemplo para "otros egresos" -->
                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'OTROS EGRESOS']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Otros egresos
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'OTROS INGRESOS']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Otros ingresos
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO SOBRANTE COB']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Abono sobrante cob
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO FALTANTE COB']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Abono faltante cob
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'SUELDO COBRADOR']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Sueldo cobrador
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'Efectivo CLi. No Regis.']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Efectivo CLi. No Regis.
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ENTREGA CAJA COBRADOR']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            Entrega caja COBRADOR
                        </a>
                    </div>

                    <div class="py-1" role="none">
                        <a href="{{ route('filament.resources.concepto-abonos.create', ['tipo' => 'ABONO DE DESCUENTO']) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100">
                            ABONO DE DESCUENTO
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- Información del cliente seleccionado -->
    @if($clienteId ?? null)
    @php
    $rutaId = session('selected_ruta_id');
    $cliente = \App\Models\Clientes::withCount(['creditos', 'abonos'])
    ->where('id_cliente', $clienteId ?? null)
    ->when($rutaId, function($query) use ($rutaId) {
    return $query->where('id_ruta', $rutaId);
    })
    ->first();
    @endphp
    @if($cliente)
    <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-600">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $cliente->nombre_completo }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">DNI: {{ $cliente->numero_documento }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <span class="font-medium">Créditos:</span> {{ $cliente->creditos_count }}
                </p>
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <span class="font-medium">Abonos:</span> {{ $cliente->abonos_count }}
                </p>
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <span class="font-medium">Estado:</span>
                    <span class="{{ $cliente->activo ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Acciones rápidas: solo visibles en vista responsive (móvil) -->
        <div class="mt-3 flex items-center gap-3 md:hidden">
            <button type="button"
                wire:click="exportExcel"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm">
                Exportar
            </button>

            <button type="button"
                wire:click="toggleCreditos"
                @if(!($clienteId ?? null)) disabled @endif
                class="inline-flex items-center px-4 py-2 rounded-md text-sm {{ ($clienteId ?? null) ? 'bg-primary-600 hover:bg-primary-700 text-white' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}">
                Créditos
            </button>
        </div>
    </div>
    @else
    @endif
    @endif

    {{-- Vista de Créditos integrada dentro de Abonos (solo en pantallas móviles) --}}
    @if(($mostrarCreditos ?? false) && ($clienteId ?? null))
        <!-- Oculta la tabla de Filament en cualquier tamaño mientras se muestran los créditos -->
        <style>
            .filament-tables-container,
            .fi-ta,
            .fi-ta-header,
            .fi-ta-toolbar,
            .fi-ta-content,
            .fi-ta-table,
            .fi-ta-footer,
            .fi-ta-pagination {
                display: none !important;
            }
        </style>

        @php
            // Cargar directamente los créditos del cliente, ordenados por fecha
            $creditosRecords = \App\Models\Creditos::with(['cliente','tipoPago','conceptosCredito','abonos'])
                ->where('id_cliente', $clienteId)
                ->orderByDesc('fecha_credito')
                ->get();

            $__creditosCount = $creditosRecords->count();
        @endphp

      

        {{-- Listado de créditos integrado --}}
        <div class="mt-3">
            @if($creditosRecords->count() > 0)
                <div class="space-y-3">
                    @foreach($creditosRecords as $record)
                        <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow p-3">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">
                                    @php $clienteNombre = optional($record->cliente)->nombre_completo; @endphp
                                    {{ $clienteNombre ? $clienteNombre : ('Crédito #' . $record->id_credito) }}
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 rounded {{ $record->saldo_actual > 0 ? 'bg-green-100 text-green-700 dark:bg-green-600 dark:text-green-100' : 'bg-red-100 text-red-700 dark:bg-red-600 dark:text-red-100' }}">
                                    {{ $record->saldo_actual > 0 ? 'ACTIVO' : 'PAGADO' }}
                                </span>
                            </div>

                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Fecha:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ optional($record->fecha_credito)->format('d/m/Y') ?? (is_string($record->fecha_credito) ? \Carbon\Carbon::parse($record->fecha_credito)->format('d/m/Y') : '') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Valor:</span>
                                    <span class="text-gray-900 dark:text-gray-100">S/ {{ number_format($record->valor_credito, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Saldo:</span>
                                    <span class="text-gray-900 dark:text-gray-100">S/ {{ number_format($record->saldo_actual, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Cuota:</span>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        @if($record->es_adicional)
                                            S/ {{ number_format($record->porcentaje_interes, 2) }}
                                        @else
                                            S/ {{ number_format($record->valor_cuota, 2) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="credito-info">
                            <span class="credito-label">Interés:</span>
                            <span class="credito-value"> {{ number_format($record->porcentaje_interes) }} %</span>
                        </div>
                                @if($record->tipoPago)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Tipo:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $record->tipoPago->nombre }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Vencimiento:</span>
                                    <span class="text-gray-900 dark:text-gray-100 {{ $record->fecha_vencimiento ? (now()->gt($record->fecha_vencimiento) ? 'text-red-600' : 'text-green-600') : '' }}">
                                        {{ optional($record->fecha_vencimiento)->format('d/m/Y') ?? (is_string($record->fecha_vencimiento) ? \Carbon\Carbon::parse($record->fecha_vencimiento)->format('d/m/Y') : '') }}
                                    </span>
                                </div>
                            </div>

                            @if($record->conceptosCredito && $record->conceptosCredito->count() > 0)
                                <div class="mt-2 text-xs text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 rounded p-2">
                                    <strong>Detalle:</strong>
                                    {{ $record->conceptosCredito->map(function($c){
                                        try { return "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2); } catch(\Throwable $e) { return "{$c->tipo_concepto}: S/ " . (string) $c->monto; }
                                    })->join(' | ') }}
                                </div>
                            @endif

                            <div class="mt-3 flex flex-row gap-2 sm:gap-3">
                                @php $tieneAbonos = $record->abonos()->exists(); @endphp
                                <a href="{{ route('filament.resources.creditos.historial-credito', ['credito' => $record->id_credito]) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs sm:text-sm font-semibold rounded-md bg-blue-600 hover:bg-blue-700 text-white">Ver Historial</a>
                                <!--
                                @if(!$tieneAbonos)
                                <button onclick="eliminarCredito({{ $record->id_credito }})" class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs sm:text-sm font-semibold rounded-md bg-red-600 hover:bg-red-700 text-white">Eliminar</button>
                                @endif
                                
                                @if($record->conceptosCredito->where('foto_comprobante', '!=', null)->isNotEmpty())
                                    <button class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs sm:text-sm font-semibold rounded-md bg-gray-600 hover:bg-gray-700 text-white" onclick="Livewire.emit('openModal', 'modal-comprobantes', {{ json_encode(['creditoId' => $record->id_credito]) }})">Ver Comprobantes</button>
                                @endif
                                -->
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-6 text-gray-600 dark:text-gray-300">
                    <div class="text-3xl mb-2">💳</div>
                    <div class="font-semibold mb-1">Sin créditos disponibles</div>
                    <div>No hay créditos que coincidan con los filtros aplicados.</div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
// WebSocket listeners para actualizaciones en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.Echo !== 'undefined') {
        // Obtener la ruta del usuario desde la sesión
        const rutaId = {{ session('selected_ruta_id', 'null') }};
        const clienteId = {{ $clienteId ?? 'null' }};
        
        if (rutaId) {
            // Suscribirse al canal público de la ruta
            const channel = window.Echo.channel(`ruta.${rutaId}`);
            
            // Escuchar eventos de abonos creados
            channel.listen('.abono.created', (data) => {
                console.log('Abono creado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('success', data.message || 'Nuevo abono registrado');
                } else {
                    // Fallback para mostrar notificación
                    console.log('Notificación:', data.message || 'Nuevo abono registrado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('$refresh');
                        console.log('Datos actualizados via Livewire');
                    }
                    
                    // Actualizar tabla si existe
                    if (typeof window.filament !== 'undefined' && window.filament.tables) {
                        // Buscar y actualizar tablas de Filament
                        const tables = document.querySelectorAll('[wire\\:id]');
                        tables.forEach(table => {
                            const wireId = table.getAttribute('wire:id');
                            if (wireId && typeof window.Livewire.find === 'function') {
                                const component = window.Livewire.find(wireId);
                                if (component) {
                                    component.call('$refresh');
                                }
                            }
                        });
                        console.log('Tablas Filament actualizadas');
                    }
                    
                    // Como último recurso, recargar solo si no hay Livewire
                    if (typeof window.Livewire === 'undefined') {
                        window.location.reload();
                    }
                }, 500);
            });
            
            // Escuchar eventos de abonos actualizados
            channel.listen('.abono.updated', (data) => {
                console.log('Abono actualizado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('info', data.message || 'Abono actualizado');
                } else {
                    console.log('Notificación:', data.message || 'Abono actualizado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('$refresh');
                        console.log('Datos actualizados via Livewire');
                    }
                    
                    // Actualizar tabla si existe
                    if (typeof window.filament !== 'undefined' && window.filament.tables) {
                        // Buscar y actualizar tablas de Filament
                        const tables = document.querySelectorAll('[wire\\:id]');
                        tables.forEach(table => {
                            const wireId = table.getAttribute('wire:id');
                            if (wireId && typeof window.Livewire.find === 'function') {
                                const component = window.Livewire.find(wireId);
                                if (component) {
                                    component.call('$refresh');
                                }
                            }
                        });
                        console.log('Tablas Filament actualizadas');
                    }
                    
                    // Como último recurso, recargar solo si no hay Livewire
                    if (typeof window.Livewire === 'undefined') {
                        window.location.reload();
                    }
                }, 500);
            });
            
            console.log('WebSocket listeners registrados para ruta:', rutaId);
        } else {
            console.log('No hay ruta seleccionada, no se pueden registrar listeners de WebSocket');
        }
    } else {
        console.error('Echo no está disponible. Verifica la configuración de WebSockets.');
    }
});
</script>
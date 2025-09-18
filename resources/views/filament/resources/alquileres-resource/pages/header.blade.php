@php
$edificioIds = $edificios ? array_keys($edificios->toArray()) : [];
$currentIndex = $edificioId ? array_search($edificioId, $edificioIds) : false;
$anteriorId = $currentIndex !== false && $currentIndex > 0 ? $edificioIds[$currentIndex - 1] : null;
$siguienteId = $currentIndex !== false && isset($edificioIds[$currentIndex + 1]) ? $edificioIds[$currentIndex + 1] :
null;
@endphp

{{-- HEADER CON BOTÓN DE AGREGAR ALQUILER --}}
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Listado de Alquileres</h1>
    
    {{-- Botones de acción del header --}}
    @if(isset($headerActions) && count($headerActions) > 0)
    <div class="flex gap-2">
        @foreach($headerActions as $action)
            {{ $action }}
        @endforeach
    </div>
    @endif
</div>

{{-- SELECTOR DE EDIFICIO CON NAVEGACIÓN Y FILTRO --}}
<div class="mb-6">
    <div class="flex items-center justify-center gap-4 mb-4">
        {{-- Botón anterior --}}
        <button wire:click="$set('edificioId', {{ $anteriorId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($anteriorId===null)>
            ◀
        </button>

        {{-- Selector de edificio con búsqueda en el centro --}}
        <div class="w-1/2 relative" x-data="{
            open: false,
            search: '',
            selectedEdificioId: @entangle('edificioId'),
            get filteredEdificios() {
                const edificios = @js($edificios ?? []);
                if (!this.search) return edificios;
                const filtered = {};
                Object.keys(edificios).forEach(id => {
                    if (edificios[id] && edificios[id].toLowerCase().includes(this.search.toLowerCase())) {
                        filtered[id] = edificios[id];
                    }
                });
                return filtered;
            },
            get selectedEdificioName() {
                if (!this.selectedEdificioId) return '-- Seleccionar Edificio --';
                const edificios = @js($edificios ?? []);
                return edificios[this.selectedEdificioId] || 'Edificio no encontrado';
            },
            selectEdificio(id, nombre) {
                this.selectedEdificioId = id === '' ? null : parseInt(id);
                this.open = false;
                this.search = '';
            }
        }">
            <label for="edificioId"
                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
                Seleccionar Edificio
            </label>

            <!-- Input/Button principal -->
            <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-left text-sm text-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500">
                <span x-text="selectedEdificioName" class="block truncate"></span>
                <svg class="w-4 h-4 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown -->
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-hidden">
                <!-- Input de búsqueda -->
                <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                    <input x-model="search" type="text" placeholder="Buscar edificio..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                </div>

                <!-- Lista de opciones -->
                <div class="max-h-48 overflow-y-auto">
                    <!-- Opción "Seleccionar" -->
                    <button @click="selectEdificio('', '-- Seleccionar Edificio --')" type="button"
                        class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                        :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedEdificioId === null }">
                        -- Seleccionar Edificio --
                    </button>

                    <!-- Opciones de edificios filtradas -->
                    <template x-for="[id, nombre] in Object.entries(filteredEdificios)" :key="id">
                        <button @click="selectEdificio(id, nombre)" type="button"
                            class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                            :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedEdificioId == id }">
                            <span x-text="nombre"></span>
                        </button>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <div x-show="Object.keys(filteredEdificios).length === 0 && search !== ''"
                        class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm">
                        No se encontraron edificios
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón siguiente --}}
        <button wire:click="$set('edificioId', {{ $siguienteId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($siguienteId===null)>
            ▶
        </button>
    </div>
</div>

{{-- SELECTOR DE DEPARTAMENTO (solo si hay edificio seleccionado) --}}
@if($edificioId && $departamentos && $departamentos->count() > 0)
<div class="mb-6">
    <div class="flex items-center justify-center gap-4">
        {{-- Selector de departamento --}}
        <div class="w-1/2 relative" x-data="{
            open: false,
            search: '',
            selectedDepartamentoId: @entangle('departamentoId'),
            get filteredDepartamentos() {
                const departamentos = @js($departamentos ?? []);
                if (!this.search) return departamentos;
                const filtered = {};
                Object.keys(departamentos).forEach(id => {
                    if (departamentos[id] && departamentos[id].toLowerCase().includes(this.search.toLowerCase())) {
                        filtered[id] = departamentos[id];
                    }
                });
                return filtered;
            },
            get selectedDepartamentoName() {
                if (!this.selectedDepartamentoId) return '-- Seleccionar Departamento --';
                const departamentos = @js($departamentos ?? []);
                return departamentos[this.selectedDepartamentoId] || 'Departamento no encontrado';
            },
            selectDepartamento(id, nombre) {
                this.selectedDepartamentoId = id === '' ? null : parseInt(id);
                this.open = false;
                this.search = '';
            }
        }">
            <label for="departamentoId"
                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
                Seleccionar Departamento
            </label>

            <!-- Input/Button principal -->
            <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-left text-sm text-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500">
                <span x-text="selectedDepartamentoName" class="block truncate"></span>
                <svg class="w-4 h-4 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown -->
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-hidden">
                <!-- Input de búsqueda -->
                <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                    <input x-model="search" type="text" placeholder="Buscar departamento..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                </div>

                <!-- Lista de opciones -->
                <div class="max-h-48 overflow-y-auto">
                    <!-- Opción "Seleccionar" -->
                    <button @click="selectDepartamento('', '-- Seleccionar Departamento --')" type="button"
                        class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                        :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedDepartamentoId === null }">
                        -- Seleccionar Departamento --
                    </button>

                    <!-- Opciones de departamentos filtradas -->
                    <template x-for="[id, nombre] in Object.entries(filteredDepartamentos)" :key="id">
                        <button @click="selectDepartamento(id, nombre)" type="button"
                            class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                            :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedDepartamentoId == id }">
                            <span x-text="nombre"></span>
                        </button>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <div x-show="Object.keys(filteredDepartamentos).length === 0 && search !== ''"
                        class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm">
                        No se encontraron departamentos
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- BOTONES DE ACCIÓN --}}
<div class="flex justify-center gap-2 mb-6">
    @if($edificioId || $departamentoId)
    <button wire:click="resetearFiltros"
        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-md transition-colors">
        Limpiar Filtros
    </button>
    @endif
</div>
<!--
{{-- INFORMACIÓN DEL EDIFICIO SELECCIONADO --}}
@if($edificio)
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    {{-- Encabezado con nombre del edificio --}}
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">{{ $edificio->nombre }}</h2>
        @if($edificio->direccion)
        <p class="text-gray-600 mt-1">{{ $edificio->direccion }}</p>
        @endif
        @if($edificio->propietario)
        <p class="text-gray-500 text-sm mt-1">Propietario: {{ $edificio->propietario->nombre_completo }}</p>
        @endif
    </div>

    {{-- Información adicional del edificio --}}
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-800">Total Departamentos</h3>
                <p class="text-2xl font-bold text-blue-600">{{ $edificio->departamentos->count() }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-green-800">Alquileres Activos</h3>
                <p class="text-2xl font-bold text-green-600">{{ $alquileresActivos }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-semibold text-yellow-800">Departamentos Disponibles</h3>
                <p class="text-2xl font-bold text-yellow-600">{{ $edificio->departamentos->count() - $alquileresActivos }}</p>
            </div>
        </div>
    </div>
</div>


@endif
-->
{{-- INFORMACIÓN DEL DEPARTAMENTO SELECCIONADO --}}
@if($departamento)
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    {{-- Encabezado con información del departamento --}}
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-800">Departamento {{ $departamento->numero_departamento }}</h3>
        <p class="text-gray-600">Piso {{ $departamento->piso }} - {{ $departamento->edificio->nombre }}</p>
    </div>

    {{-- Detalles del departamento --}}
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-semibold text-gray-700 mb-2">Información General</h4>
                <p><span class="font-medium">Estado:</span> {{ $departamento->estado->nombre ?? 'N/A' }}</p>
                @if($departamento->precio_alquiler)
                <p><span class="font-medium">Precio Sugerido:</span> S/
                    {{ number_format($departamento->precio_alquiler, 2) }}</p>
                @endif
            </div>
            @if($departamento->alquilerActivo)
            <div>
                <h4 class="font-semibold text-gray-700 mb-2">Alquiler Actual</h4>
                <p><span class="font-medium">Inquilino:</span>
                    {{ $departamento->alquilerActivo->inquilino->nombre_completo }}</p>
                <p><span class="font-medium">Precio:</span> S/
                    {{ number_format($departamento->alquilerActivo->precio_mensual, 2) }}</p>
                <p><span class="font-medium">Desde:</span>
                    {{ $departamento->alquilerActivo->fecha_inicio->format('d/m/Y') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
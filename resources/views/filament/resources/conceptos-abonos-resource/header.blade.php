@php
$userIds = array_keys($users->toArray());
$currentIndex = array_search($userId, $userIds);
$anteriorId = $currentIndex > 0 ? $userIds[$currentIndex - 1] : null;
$siguienteId = isset($userIds[$currentIndex + 1]) ? $userIds[$currentIndex + 1] : null;
@endphp

{{-- SELECTOR DE USUARIO CON NAVEGACIÓN Y FILTRO --}}
<div class="mb-6">
    <div class="flex items-center justify-center gap-4 mb-4">
        {{-- Botón anterior --}}
        <button type="button"
            wire:click="$set('userId', {{ $anteriorId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($anteriorId===null)>
            ◀
        </button>

        {{-- Selector con búsqueda en el centro --}}
        <div class="w-1/2 relative" x-data="{
            open: false,
            search: '',
            selectedUserId: @entangle('userId'),
            get filteredUsers() {
                if (!this.search) return @js($users);
                const users = @js($users);
                const filtered = {};
                Object.keys(users).forEach(id => {
                    if (users[id].toLowerCase().includes(this.search.toLowerCase())) {
                        filtered[id] = users[id];
                    }
                });
                return filtered;
            },
            get selectedUserName() {
                if (!this.selectedUserId) return '-- Seleccionar Usuario --';
                const users = @js($users);
                return users[this.selectedUserId] || 'Usuario no encontrado';
            },
            selectUser(id) {
                this.selectedUserId = id === '' ? null : parseInt(id);
                this.open = false;
                this.search = '';
            }
        }">
            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
                Seleccionar Usuario
            </label>

            <!-- Input/Button principal -->
            <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-left text-sm text-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500">
                <span x-text="selectedUserName" class="block truncate"></span>
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
                    <input x-model="search" type="text" placeholder="Buscar usuario..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                </div>

                <!-- Lista de opciones -->
                <div class="max-h-48 overflow-y-auto">
                    <!-- Opción "Seleccionar" -->
                    <button @click="selectUser('')" type="button"
                        class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                        :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedUserId === null }">
                        -- Todos los Usuarios --
                    </button>

                    <!-- Opciones de usuarios filtradas -->
                    <template x-for="[id, name] in Object.entries(filteredUsers)" :key="id">
                        <button @click="selectUser(id)" type="button"
                            class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                            :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedUserId == id }">
                            <span x-text="name"></span>
                        </button>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <div x-show="Object.keys(filteredUsers).length === 0 && search !== ''"
                        class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm">
                        No se encontraron usuarios
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón siguiente --}}
        <button type="button"
            wire:click="$set('userId', {{ $siguienteId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($siguienteId===null)>
            ▶
        </button>
    </div>
</div>

{{-- FILTRO DE FECHAS --}}
<div class="flex flex-col sm:flex-row items-center gap-4 lg:gap-5 mb-6">
    <!-- Componente unificado de filtro de fechas -->
    <div class="relative inline-block text-left" x-data="{ open: false }">
        <!-- Botón desplegable -->
        <button @click="open = !open"
            class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600">
            <x-heroicon-o-calendar class="w-4 h-4 text-gray-600 dark:text-gray-300" />
            {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d M Y') : 'Desde' }}
            -
            {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d M Y') : 'Hasta' }}
            <svg class="w-4 h-4 ml-1 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            class="absolute z-50 mt-2 w-90 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 p-4 space-y-3">
            <!-- Selector de período -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Período:</label>
                <select wire:model="fechaPeriodo" wire:change="aplicarPeriodoFecha($event.target.value)"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="hoy">Hoy</option>
                    <option value="ayer">Ayer</option>
                    <option value="semana">Esta semana</option>
                    <option value="semana_anterior">Semana pasada</option>
                    <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                    <option value="mes">Este mes</option>
                    <option value="mes_anterior">Mes pasado</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>

            <!-- Tipo de fecha -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Tipo de fecha:</label>
                <select wire:model="tipoFecha"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="created_at">Creación Registro</option>
                    <option value="fecha_concepto">Fecha Concepto</option>
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

    <!-- Botón Limpiar -->
    <div class="flex gap-2">
        @if($userId || $fechaPeriodo != 'hoy')
        <button wire:click="resetearFiltros"
            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-md transition-colors">
            Limpiar Filtros
        </button>
        @endif
    </div>
</div>

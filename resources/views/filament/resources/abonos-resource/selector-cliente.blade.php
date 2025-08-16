<div class="mb-6 bg-white p-4 rounded-lg shadow">
    <label for="cliente-selector" class="block text-sm font-medium text-gray-700 mb-2">
        Seleccionar Cliente
    </label>

    <div class="flex-1 relative" x-data="{
        open: false,
        search: '',
        selectedClienteId: @entangle('cliente_id'),
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
            $wire.call('cargarDatosCliente', id === '' ? null : parseInt(id));
        }
    }">
        <!-- Input/Button principal -->
        <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-left focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <span x-text="selectedClienteName" class="block truncate"></span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-hidden">
            <!-- Input de búsqueda -->
            <div class="p-2 border-b border-gray-200">
                <input x-model="search" type="text" placeholder="Buscar cliente..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <!-- Lista de opciones -->
            <div class="max-h-48 overflow-y-auto">
                <!-- Opción "Todos los clientes" -->
                <button @click="selectCliente('', 'Todos los clientes')" type="button"
                    class="w-full px-3 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                    :class="{ 'bg-indigo-50 text-indigo-600': selectedClienteId === null }">
                    Todos los clientes
                </button>

                <!-- Opciones de clientes filtradas -->
                <template x-for="[id, nombre] in Object.entries(filteredClientes)" :key="id">
                    <button @click="selectCliente(id, nombre)" type="button"
                        class="w-full px-3 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                        :class="{ 'bg-indigo-50 text-indigo-600': selectedClienteId == id }">
                        <span x-text="nombre"></span>
                    </button>
                </template>

                <!-- Mensaje cuando no hay resultados -->
                <div x-show="Object.keys(filteredClientes).length === 0 && search !== ''"
                    class="px-3 py-2 text-gray-500 text-sm">
                    No se encontraron clientes
                </div>
            </div>
        </div>
    </div>
</div>
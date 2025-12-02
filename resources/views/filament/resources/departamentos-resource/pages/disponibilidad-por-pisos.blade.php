<x-filament::page>
    <div class="space-y-6">
        <!-- Selector de Edificio -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label for="edificio-select" class="text-sm font-medium text-gray-700">
                        Seleccionar Edificio:
                    </label>
                    <select id="edificio-select" wire:model="selectedEdificio"
                        class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">-- Seleccione un edificio --</option>
                        @foreach($edificios as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                @if($selectedEdificio)
                <div class="text-sm text-gray-500">
                    Total de pisos: {{ count($departamentosPorPiso) }}
                </div>
                @endif
            </div>
        </div>

        @if($selectedEdificio && !empty($departamentosPorPiso))
        <!-- Título -->
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                Disponibilidad por Pisos - {{ $edificios[$selectedEdificio] }}
            </h2>
        </div>

        <!-- Departamentos por Piso -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="space-y-6">
                @foreach($departamentosPorPiso as $piso => $departamentos)
                <div class="flex items-start space-x-6">
                    <!-- Etiqueta del Piso -->
                    <div class="bg-gray-900 text-white px-3 py-2 rounded-lg min-w-[60px] text-center flex-shrink-0">
                        <div class="text-xs font-medium uppercase tracking-wide">PISO</div>
                        <div class="text-lg font-bold">{{ $piso }}</div>
                    </div>

                    <!-- Departamentos del Piso -->
                    <div class="flex flex-wrap gap-3 flex-1">
                        @foreach($departamentos as $departamento)
                        @php
                        $estado = $departamento['estado']['nombre'] ?? 'Disponible';
                        $color = $estadosColores[$estado] ?? '#10B981'; // Verde por defecto
                        $backgroundStyle = $this->getBackgroundStyle($color);
                        @endphp

                        <div class="text-white rounded-lg p-4 min-w-[85px] text-center shadow-sm hover:shadow-lg transition-all duration-200 cursor-pointer transform hover:scale-105 relative group"
                            style="{{ $backgroundStyle }}"
                            onclick="openDepartmentModal({{ json_encode($departamento) }})">
                            <div class="text-sm font-bold mb-1">{{ $departamento['numero_departamento'] }}</div>

                            <div class="text-xs opacity-90">
                                <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 2h8v8H6V6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 h-6 bg-black/20 text-white text-[11px] leading-6 font-medium tracking-wide cursor-pointer rounded-b-lg"
                                onclick="openHistorial(event, {{ json_encode($departamento) }})">
                                Historial
                            </div>

                            <!-- Tooltip -->
                            <div
                                class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-white text-gray-800 text-xs rounded-lg shadow-lg border border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-[99999] whitespace-nowrap">
                                <div class="font-bold">Departamento Nro. {{ $departamento['numero_departamento'] }}
                                </div>
                                <div class="text-xs mt-1">
                                    <div class="text-left"><span class="font-bold">Tipo moneda:</span>
                                        {{ $departamento['tipo_moneda'] ?? 'Soles' }}</div>
                                    <div class="text-left"><span class="font-bold">Precio:</span> S/
                                        {{ number_format($departamento['precio_alquiler'] ?? 0, 2) }}</div>
                                    <div class="mt-1">
                                        <div class="text-left"><span class="font-bold">Nro Hab:</span>
                                            {{ $departamento['cuartos'] ?? '1' }}</div>
                                        <div class="text-left"><span class="font-bold">Nro Baños:</span>
                                            {{ $departamento['banos'] ?? '1' }}</div>
                                        <div class="text-left"><span class="font-bold">Área TOTAL:</span>
                                            {{ $departamento['metros_cuadrados'] ?? '0' }}m2</div>
                                    </div>
                                    <div class="mt-1">
                                        <div class="font-bold text-orange-600">INMUEBLES EN OPERACIÓN</div>
                                        <div class="text-left"><span class="font-bold">Departamento:</span>
                                            {{ $departamento['numero_departamento'] }}</div>
                                        <div class="text-left"><span class="font-bold">Precio:</span> S/
                                            {{ number_format($departamento['precio_alquiler'] ?? 0, 2) }}</div>
                                        <div class="mt-1">
                                            <div class="text-left"><span class="font-bold">Estado:</span> {{ $estado }}
                                            </div>
                                            @if(isset($departamento['alquileres'][0]['inquilino']))
                                            @php
                                            $inquilino = $departamento['alquileres'][0]['inquilino'];
                                            $nombreCompleto = ($inquilino['nombre'] ?? '') . ' ' .
                                            ($inquilino['apellido'] ?? '');
                                            @endphp
                                            <div class="text-left"><span class="font-bold">Cliente:</span>
                                                {{ trim($nombreCompleto) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- Flecha del tooltip -->
                                <div
                                    class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-white">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Leyenda de Estados -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Leyenda de Estados</h3>
            <div class="flex flex-wrap gap-4 justify-start">
                @foreach($estadosColores as $estado => $color)
                @php
                $backgroundStyle = $this->getBackgroundStyle($color);
                @endphp

                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-6 h-6 rounded-lg shadow-sm" style="{{ $backgroundStyle }}"></div>
                    <span class="text-sm font-medium text-gray-700">{{ $estado }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @elseif($selectedEdificio)
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        No se encontraron departamentos activos para el edificio seleccionado.
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Seleccione un edificio para ver la disponibilidad de departamentos por pisos.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Tabla de Listado de Departamentos -->
        @if($selectedEdificio && !empty($todosDepartamentos))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Listado de Departamentos -
                    {{ $edificios[$selectedEdificio] }}</h3>
                <p class="text-sm text-gray-500">Total de departamentos: {{ count($todosDepartamentos) }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Número</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Piso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cuartos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Baños</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                M²</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($todosDepartamentos as $departamento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $departamento['id_departamento'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $departamento['numero_departamento'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $departamento['piso'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $departamento['cuartos'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $departamento['banos'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/
                                {{ number_format($departamento['precio_alquiler'] ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                $estado = $departamento['estado']['nombre'] ?? 'Disponible';
                                $colorClass = match($estado) {
                                'Disponible' => 'bg-green-100 text-green-800',
                                'Ocupado' => 'bg-red-100 text-red-800',
                                'Mantenimiento' => 'bg-yellow-100 text-yellow-800',
                                'Reservado' => 'bg-blue-100 text-blue-800',
                                'Desocupado' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                                };
                                @endphp
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colorClass }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $departamento['metros_cuadrados'] ?? '0' }}m²</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal para mostrar foto del departamento -->
    <div id="departmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Departamento</h3>
                    <button onclick="closeDepartmentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="modalContent">
                    <!-- El contenido se llenará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function openDepartmentModal(departamento) {
        const modal = document.getElementById('departmentModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');

        modalTitle.textContent = `Departamento ${departamento.numero_departamento}`;

        let content = `
                <div class="space-y-4">
            `;

        if (departamento.foto_path) {
            content += `
                    <div class="mt-4">
                        <strong>Foto del Departamento:</strong>
                        <div class="mt-2">
                            <img src="/storage/${departamento.foto_path}" alt="Foto del departamento ${departamento.numero_departamento}" class="w-full h-80 rounded-lg shadow-lg object-contain bg-gray-100">
                        </div>
                    </div>
                `;
        } else {
            content += `
                    <div class="mt-4 p-4 bg-gray-100 rounded-lg text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No hay foto disponible para este departamento</p>
                    </div>
                `;
        }

        content += '</div>';
        modalContent.innerHTML = content;
        modal.classList.remove('hidden');
    }

    function closeDepartmentModal() {
        const modal = document.getElementById('departmentModal');
        modal.classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('departmentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDepartmentModal();
        }
    });

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDepartmentModal();
        }
    });

    function openHistorial(e, departamento) {
        if (e) e.stopPropagation();
        const id = departamento.id_departamento || departamento.id;
        if (id) {
            window.location.href = `/departamentos/${id}/historial`;
        }
    }
    </script>
</x-filament::page>
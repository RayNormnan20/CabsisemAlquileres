<x-filament::page>
    <div class="space-y-4">
        <x-filament::card>
            <div class="px-4 pt-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Historial de Alquiler —
                    {{ $this->departamento->edificio->nombre ?? 'N/A' }} /
                    {{ $this->departamento->numero_departamento }}</h2>
                @if(!$this->tieneActivo)
                <a href="{{ \App\Filament\Resources\AlquileresResource::getUrl('create') }}?prefillDepartamento={{ $this->departamento->id_departamento }}"
                    class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-md">Crear
                    Alquiler</a>
                @endif
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Inicio</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fin</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precio</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->alquileres as $alquiler)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <button wire:click="verPagos({{ $alquiler->id_alquiler }})"
                                    class="text-blue-600 hover:text-blue-800">
                                    {{ $alquiler->inquilino->nombre_completo ?? 'N/A' }}
                                </button>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ $alquiler->fecha_fin ? \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ ucfirst($alquiler->estado_alquiler) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">S/
                                {{ number_format($alquiler->precio_mensual, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                @if($alquiler->estado_alquiler === 'activo')
                                <button wire:click="abrirConfirmacion({{ $alquiler->id_alquiler }})"
                                    class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded">Finalizar
                                    Alquiler</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">Sin registros</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>

        @if(!empty($this->pagosCliente))
        <x-filament::card>
            <div class="px-4 pt-4">
                <h3 class="text-md font-semibold">Abonos del Cliente</h3>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Método</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Comprobantes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->pagosCliente as $pago)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($pago['fecha_pago'])->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">S/ {{ number_format($pago['monto_pagado'], 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ ucfirst($pago['metodo_pago']) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                {{ $pago['usuario_registro']['name'] ?? ($pago['usuarioRegistro']['name'] ?? 'N/A') }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                @php
                                $tieneFotos = !empty($pago['foto_1_path']) || !empty($pago['foto_2_path']) ||
                                !empty($pago['foto_3_path']);
                                @endphp
                                @if($tieneFotos)
                                <button wire:click="abrirFotosPago({{ $pago['id_pago_alquiler'] }})"
                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-blue-600 hover:bg-blue-700 text-white">
                                    Ver fotos
                                </button>
                                @else
                                —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
        @endif

        @if($this->confirmandoFinalizacion)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="cancelarConfirmacion"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w全文 max-w-md p-6">
                <h3 class="text-lg font-semibold mb-2">Confirmar finalización</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">¿Desea finalizar este alquiler? El departamento
                    pasará a estado Disponible.</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="cancelarConfirmacion"
                        class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded">Cancelar</button>
                    <button wire:click="finalizarAlquiler({{ $this->alquilerAConfirmarId }})"
                        class="px-3 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded">Finalizar</button>
                </div>
            </div>
        </div>
        @endif

        @if($this->visualizandoFotos)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/60" wire:click="cerrarFotosPago"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl p-4">
                <div x-data="{ i: 0, fotos: @js($this->fotosPago) }" class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-md font-semibold">Fotos del Pago</h3>
                        <div class="text-sm text-gray-500">Imagen <span x-text="i + 1"></span>/<span
                                x-text="fotos.length"></span></div>
                    </div>
                    <div
                        class="relative w-full h-[360px] bg-gray-100 dark:bg-gray-800 flex items-center justify-center rounded">
                        <template x-if="fotos.length > 0">
                            <img :src="fotos[i]" alt="Foto" class="max-h-[340px] max-w-full rounded shadow" />
                        </template>
                        <template x-if="fotos.length === 0">
                            <div class="text-gray-500">Sin fotos</div>
                        </template>
                        <button type="button"
                            class="absolute left-2 top-1/2 -translate-y-1/2 px-3 py-2 bg-gray-700/70 text-white rounded"
                            @click="i = (i - 1 + fotos.length) % fotos.length" x-show="fotos.length > 1">◀</button>
                        <button type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-2 bg-gray-700/70 text-white rounded"
                            @click="i = (i + 1) % fotos.length" x-show="fotos.length > 1">▶</button>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button wire:click="cerrarFotosPago"
                            class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament::page>
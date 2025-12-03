<x-filament::page>
    <div class="space-y-4">
        <x-filament::card>
            <div class="px-4 pt-4">
                <h2 class="text-lg font-semibold">Historial de Alquiler — {{ $this->departamento->edificio->nombre ?? 'N/A' }} / {{ $this->departamento->numero_departamento }}</h2>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inicio</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->alquileres as $alquiler)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $alquiler->inquilino->nombre_completo ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $alquiler->fecha_fin ? \Carbon\Carbon::parse($alquiler->fecha_fin)->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ ucfirst($alquiler->estado_alquiler) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">S/ {{ number_format($alquiler->precio_mensual, 2) }}</td>
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
    </div>
</x-filament::page>


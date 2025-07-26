<x-filament::widget>
    <x-filament::card>
        {{-- ELIMINA 'max-w-none' de este div si está presente --}}
        <div class="w-full bg-white shadow rounded-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ strtoupper($record->cliente->nombre_completo ?? 'SIN NOMBRE') }}
                    </h2>

                    <p class="text-sm text-gray-600 mt-2">
                        <strong>DNI:</strong> {{ $record->cliente->numero_documento ?? 'N/D' }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Teléfono:</strong> {{ $record->cliente->celular ?? $record->cliente->telefono ?? 'N/D' }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Dirección:</strong> {{ $record->cliente->direccion ?? 'No especificada' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">
                        <strong>Fecha de Crédito:</strong> {{ $record->fecha_credito?->format('d M Y') ?? 'N/D' }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Monto:</strong> S/ {{ number_format($record->valor_credito, 2, '.', ',') }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Saldo Actual:</strong> S/ {{ number_format($record->saldo_actual, 2, '.', ',') }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Cuotas:</strong> {{ $record->numero_cuotas }} (Pagadas: {{ $record->cuotas_pagadas }}, Pendientes: {{ $record->cuotas_pendientes }})
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Estado:</strong>
                        <span class="font-semibold {{ $record->saldo_actual > 0 ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $record->saldo_actual > 0 ? 'Crédito Activo' : 'Crédito Cancelado' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
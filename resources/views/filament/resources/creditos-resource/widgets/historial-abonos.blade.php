<x-filament::widget>
    <x-filament::card>
        {{-- Tabla de abonos --}}
        {{ $this->table }}

        {{-- Pie con el total y saldo final --}}
        <div
            class="mt-4 px-4 py-3 border-t flex justify-between items-center text-base text-gray-800 dark:text-gray-100 font-bold">
            <div class="flex items-center space-x-2">
                <span class="uppercase">Total Abonos:</span>
                <span class="text-primary-600 text-lg">S/ {{ number_format($totalCantidad, 2) }}</span>
            </div>

            <div class="flex items-center space-x-2">
                <span class="uppercase">Saldo Actual:</span>
                <span class="{{ $ultimoSaldoPosterior < 0 ? 'text-red-600' : 'text-green-600' }} text-lg">
                    S/ {{ number_format($ultimoSaldoPosterior, 2) }}
                </span>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

<div class="mt-6 border-t pt-4 bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-none">
    <div wire:poll.5s class="px-4 py-3 flex flex-wrap gap-x-8 gap-y-4 w-full">
        @foreach($this->getUsuariosResumen() as $usuario)
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-2">
                <span class="text-lg font-medium text-gray-800 dark:text-gray-200">
                    {{ $usuario['name'] }}
                </span>

                @if(($usuario['efectivo'] ?? 0) > 0)
                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                        Efectivo: S/ {{ number_format($usuario['efectivo'], 2) }}
                    </span>
                @endif

                @if(($usuario['yape'] ?? 0) > 0)
                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                        Yape: S/ {{ number_format($usuario['yape'], 2) }}
                    </span>
                @endif
            </div>
        @endforeach
    </div>
</div>
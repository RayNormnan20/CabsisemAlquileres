@php
    $rutaId = session('selected_ruta_id');
    $query = \App\Models\Clientes::query();
    if ($rutaId) {
        $query->deRuta($rutaId);
    }
    $clientesPorRenovar = $query->whereHas('creditos', function ($q) {
        $q->where('por_renovar', true);
    })->limit(10)->get();
@endphp

<div {{ $attributes->class([
    'flex flex-col items-center justify-center mx-auto my-6 space-y-4 text-center bg-white',
    'dark:bg-gray-800' => config('notifications.dark_mode'),
]) }}>
    @if ($clientesPorRenovar->count() == 0)
        <div @class([
            'flex items-center justify-center w-12 h-12 text-primary-500 rounded-full bg-primary-50',
            'dark:bg-gray-700' => config('notifications.dark_mode'),
        ])>
            <x-heroicon-o-bell class="w-5 h-5" />
        </div>
    @endif

    <div class="w-full max-w-md">
        @if ($clientesPorRenovar->count() > 0)
            <div class="w-full mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700 text-left">
                <div class="px-4 py-3 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-900/30 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-primary-700 dark:text-primary-400 flex items-center gap-2">
                        <x-heroicon-o-sparkles class="w-5 h-5" />
                        Clientes por renovar
                    </h2>
                    <span class="px-2 py-0.5 text-xs font-medium bg-primary-100 text-primary-700 rounded-full dark:bg-primary-900 dark:text-primary-300">
                        {{ $clientesPorRenovar->count() }}
                    </span>
                </div>

                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($clientesPorRenovar as $c)
                        <li>
                            <a
                                href="{{ \App\Filament\Resources\CreditosResource::getUrl('index', ['cliente_id' => $c->id_cliente]) }}"
                                class="group flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-900 dark:group-hover:text-primary-400 transition-colors">
                                        <x-heroicon-o-user class="w-4 h-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                            {{ $c->nombre_completo ?? ($c->nombre ?? 'Cliente') }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Listo para renovar
                                        </span>
                                    </div>
                                </div>
                                <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="text-center space-y-2">
                <h2 @class([
                    'text-lg font-bold tracking-tight',
                    'dark:text-white' => config('notifications.dark_mode'),
                ])>
                    Sin notificaciones
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No tienes notificaciones pendientes ni clientes por renovar.
                </p>
            </div>
        @endif
    </div>
</div>

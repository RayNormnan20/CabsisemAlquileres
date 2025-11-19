<div {{ $attributes->class([
    'flex flex-col items-center justify-center mx-auto my-6 space-y-4 text-center bg-white',
    'dark:bg-gray-800' => config('notifications.dark_mode'),
]) }}>
    <div @class([
        'flex items-center justify-center w-12 h-12 text-primary-500 rounded-full bg-primary-50',
        'dark:bg-gray-700' => config('notifications.dark_mode'),
    ])>
        <x-heroicon-o-bell class="w-5 h-5" />
    </div>

    <div class="max-w-md space-y-2">
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

        @if ($clientesPorRenovar->count() > 0)
            <h2 @class([
                'text-lg font-bold tracking-tight',
                'dark:text-white' => config('notifications.dark_mode'),
            ])>
                Clientes por renovar
            </h2>

            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                @foreach ($clientesPorRenovar as $c)
                    <li>
                        <a
                            href="{{ \App\Filament\Resources\CreditosResource::getUrl('index', ['cliente_id' => $c->id_cliente]) }}"
                            class="text-primary-600 hover:underline dark:text-primary-400"
                        >
                            {{ $c->nombre_completo ?? ($c->nombre ?? 'Cliente') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
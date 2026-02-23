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

    <div class="w-full max-w-md">
        <div class="text-center space-y-2">
            <h2 @class([
                'text-lg font-bold tracking-tight',
                'dark:text-white' => config('notifications.dark_mode'),
            ])>
                Sin notificaciones
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No tienes notificaciones pendientes.
            </p>
        </div>
    </div>
</div>

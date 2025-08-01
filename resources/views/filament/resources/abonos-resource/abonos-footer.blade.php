<div class="mt-6 border-t pt-4 bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-none">
    <!--
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 px-4 pt-3">
        Totales por Usuario
    </h3>
    -->
   <div wire:loading.remove class="px-4 py-3 flex flex-wrap gap-x-8 gap-y-4 w-full">
    @foreach($this->getFooterData() as $usuario)
        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-2">
            <span class="text-lg font-medium text-gray-800 dark:text-gray-200">
                {{ $usuario->name }}
            </span>
            <span class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                S/ {{ number_format($usuario->total_abonos, 2) }}
            </span>
        </div>
    @endforeach
    </div>
</div>

<div class="flex items-center gap-2">
    <button
        type="button"
        x-data="{}"
        x-on:click="$dispatch('open-modal', { id: 'modal-con-select' })"
        class="
            inline-flex items-center justify-center
            rounded-lg px-4 py-2 text-sm font-semibold
            bg-blue-600 text-white
            hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
            transition-all duration-200 shadow-md
        "
    >
        <span>{{ $buttonText ?? 'Ingressos y Gastos' }}</span>
    </button>
    
    <!-- Dropdown de configuración -->
    <div class="relative" x-data="{ open: false }">
        <!-- Ícono de configuración -->
        <button
            type="button"
            @mouseenter="open = true"
            @mouseleave="open = false"
            class="
                inline-flex items-center justify-center
                w-10 h-10 rounded-lg
                bg-gray-100 text-gray-600
                hover:bg-gray-200 hover:text-gray-800
                focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                transition-all duration-200 shadow-sm
                dark:bg-gray-700 dark:text-gray-300
                dark:hover:bg-gray-600 dark:hover:text-white
            "
            title="Configuración"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </button>

        <!-- Dropdown menu -->
        <div 
            x-show="open"
            @mouseenter="open = true"
            @mouseleave="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="
                absolute right-0 top-full mt-2 w-56
                bg-white dark:bg-gray-800
                rounded-lg shadow-lg border border-gray-200 dark:border-gray-700
                py-2 z-50
            "
            style="display: none;"
        >
            <!-- Opción: Registro de Actividad -->
            <a 
                href="{{ route('filament.resources.log-actividads.index') }}"
                class="
                    flex items-center px-4 py-3
                    text-sm text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    hover:text-gray-900 dark:hover:text-white
                    transition-colors duration-150
                "
            >
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <div class="font-medium">Registro de Actividad</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Ver historial de actividades</div>
                </div>
            </a>
        </div>
    </div>
</div>
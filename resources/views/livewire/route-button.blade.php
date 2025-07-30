


<div>

    <button
        type="button"
        x-data="{}" {{-- x-data={} es necesario para que Alpine.js procese x-on:click --}}
        x-on:click="$dispatch('open-modal', { id: 'modal-con-select' })"
        class="
            fi-btn fi-btn-size-md fi-btn-color-gray fi-btn-outline
            inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75
            focus:border-primary-600 focus:ring-primary-600 disabled:pointer-events-none disabled:opacity-70
            dark:focus:border-primary-500 dark:focus:ring-primary-500
            dark:hover:bg-gray-700
        "
    >
        <span>{{ $buttonText ?? 'Ruta' }}</span>
    </button>

</div>
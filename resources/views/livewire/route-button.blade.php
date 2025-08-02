<div>
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
</div>
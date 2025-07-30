<x-filament::modal
    id="modal-con-select"
    x-ref="modal"
    width="md"
    x-cloak
    x-show="$wire.showModal" {{-- $wire aquí se refiere a ModalWithSelect --}}
    x-on:open-modal.window="if ($event.detail.id == 'modal-con-select') $wire.set('showModal', true)"
    x-on:close-modal.window="if ($event.detail.id == 'modal-con-select') $wire.set('showModal', false)"
    x-on:click.outside="$wire.set('showModal', false)"
    x-on:keydown.escape.window="$wire.set('showModal', false)"
>
    <x-slot name="heading">
        Selecciona una Ruta
    </x-slot>

    <x-slot name="description">
        Por favor, elige una de las opciones disponibles.
    </x-slot>

    {{-- El select dentro del modal --}}
    <div class="mt-4">
        <label for="select-ruta-modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rutas disponibles:</label>
        <select
            id="select-ruta-modal"
            wire:model="selectedOption" {{-- Enlaza con la propiedad de ModalWithSelect --}}
            class="
                fi-select-input
                w-full
                rounded-lg
                border-gray-300
                bg-white
                py-2
                ps-3
                pe-8
                text-gray-950
                shadow-sm
                outline-none
                transition
                duration-75
                placeholder:text-gray-400
                focus:border-primary-500
                focus:ring-primary-500
                disabled:pointer-events-none
                disabled:opacity-70
                dark:border-gray-600
                dark:bg-gray-700
                dark:text-white
                dark:placeholder:text-gray-500
                dark:focus:border-primary-500
                dark:focus:ring-primary-500
                sm:text-sm
                sm:leading-6
            "
        >
            <option value="">-- Seleccionar --</option> {{-- Opción por defecto para no seleccionar ninguna ruta --}}
            @foreach ($rutas as $ruta) {{-- Itera sobre las rutas pasadas desde el componente --}}
                <option value="{{ $ruta->id_ruta }}">
                    {{ $ruta->nombre_completo ?? $ruta->nombre }} {{-- Usa el accesorio o solo el nombre --}}
                </option>
            @endforeach
        </select>
    </div>

    <x-filament::button
        wire:click="confirmSelection"
        color="primary"
        class="mt-4"
        :disabled="!$selectedOption"
    >
        Seleccionar
    </x-filament::button>


    <x-filament::button
        wire:click="closeModal" {{-- Llama al método closeModal de ModalWithSelect --}}
        color="secondary"
        class="mt-4"
    >
        Cerrar
    </x-filament::button>
</x-filament::modal>
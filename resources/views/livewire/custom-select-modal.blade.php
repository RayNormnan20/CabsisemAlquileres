<!-- <div
    x-data="{ showModal: false }"
    x-on:close-modal.window="showModal = false" {{-- Escucha el evento para cerrar el modal --}}
>
    <label for="my-custom-select" class="sr-only">Seleccionar opción</label>
    <select
        id="my-custom-select"
        class="
            fi-select-input
            w-full
            rounded-lg
            border-none
            bg-transparent
            py-1.5
            pe-8
            ps-3
            text-gray-950
            outline-none
            transition
            duration-75
            placeholder:text-gray-400
            focus:border-primary-500
            focus:ring-primary-500
            disabled:pointer-events-none
            disabled:opacity-70
            dark:text-white
            dark:placeholder:text-gray-500
            dark:focus:border-primary-500
            dark:focus:ring-primary-500
            sm:text-sm
            sm:leading-6
            [&:not(select)]:pe-3
            [&:not(select)]:ps-3
            [&_option:checked]:bg-gray-50
            [&_option:checked]:dark:bg-gray-800
        "
        wire:click="openModal" {{-- Cuando se haga clic, llama al método openModal en Livewire --}}
    >
        <option value="">Seleccionar...</option>
        <option value="option1">Opción 1</option>
        <option value="option2">Opción 2</option>
        <option value="option3">Opción 3</option>
    </select>

    {{-- El modal de Filament --}}
    <x-filament::modal
        id="my-custom-modal"
        x-ref="modal"
        width="md"
        x-cloak
        x-show="showModal"
        x-on:open-modal.window="if ($event.detail.id == 'my-custom-modal') showModal = true"
        x-on:close-modal.window="if ($event.detail.id == 'my-custom-modal') showModal = false"
        x-on:click.outside="showModal = false"
        {{-- x-on:keydown.escape.window="showModal = false" --}} {{-- descomentar si quieres cerrar con escape --}}
    >
        <x-slot name="heading">
            Título del Modal
        </x-slot>

        <x-slot name="description">
            Este es el contenido de tu modal, abierto desde el select.
        </x-slot>

        {{-- Aquí puedes poner contenido más complejo, un formulario, etc. --}}
        <p>Puedes poner cualquier contenido aquí, como un formulario de Filament.</p>

        <x-filament::button
            wire:click="closeModal"
            color="secondary"
            class="mt-4"
        >
            Cerrar
        </x-filament::button>

        {{-- Si el modal tiene un formulario, aquí lo renderizarías --}}
        {{-- {{ $this->form }} --}}
    </x-filament::modal>
</div> -->
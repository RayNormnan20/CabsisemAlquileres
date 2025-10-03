@php /** @var \Filament\Pages\Page $this */ @endphp
<x-filament::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="space-y-2">
                <h2 class="text-lg font-semibold">Horarios de acceso</h2>
                <p class="text-sm text-gray-600">Administra las horas permitidas para cada usuario.</p>
            </div>
        </x-filament::card>

        <x-filament::widgets
            :widgets="$this->getWidgets()"
            :columns="1"
        />
    </div>
</x-filament::page>
<x-filament::card>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Filtros de Búsqueda
            </h3>

            <x-filament::button color="success" icon="heroicon-o-document-download" wire:click="exportarPdf" size="sm">
                Exportar PDF
            </x-filament::button>
        </div>
    </div>

    {{ $this->form }}
    </div>
</x-filament::card>

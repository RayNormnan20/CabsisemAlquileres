<x-filament::page>
    <div class="space-y-6">
        <!-- Formulario de selección de usuario con botones de exportación -->
        <x-filament::card>
            <div class="p-4">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Seleccione un Usuario</h2>
                    
                    <!-- Botones de exportación (solo visibles cuando hay usuario seleccionado) -->
                    @if($this->userId)
                        <div class="flex gap-2">
                            <!-- Botón PDF -->
                            <button wire:click.prevent class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </button>
                            
                            <!-- Botón Excel -->
                            <button wire:click.prevent class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </button>
                        </div>
                    @endif
                </div>
                
                {{ $this->form }}
            </div>
        </x-filament::card>
        
        <!-- Widget de créditos y abonos -->
        <div>
            @if($this->userId)
                @livewire(\App\Filament\Widgets\ClienteCreditosAbonosWidget::class, [
                    'wire:key' => "usuario-creditos-abonos-{$this->userId}",
                    'userId' => $this->userId,
                ])
            @endif
        </div>
    </div>
</x-filament::page>
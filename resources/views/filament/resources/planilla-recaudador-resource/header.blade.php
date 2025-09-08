<style>
    /* Ocultar completamente la tabla principal de Filament */
    .fi-ta-table,
    .fi-ta-content,
    .fi-ta-header,
    .fi-ta-empty-state,
    .fi-ta-pagination,
    .fi-ta-filters,
    .fi-ta-actions,
    .fi-ta-bulk-actions,
    .fi-ta-search,
    .fi-ta-toolbar,
    .fi-table,
    .fi-table-container,
    .fi-table-header,
    .fi-table-body,
    .fi-table-footer,
    .fi-table-empty-state,
    .fi-table-pagination,
    .fi-table-filters,
    .fi-table-actions,
    .fi-table-bulk-actions,
    .fi-table-search,
    .fi-table-toolbar,
    [data-table],
    [data-table-container],
    [data-table-header],
    [data-table-body],
    [data-table-footer],
    [data-table-empty-state],
    [data-table-pagination],
    [data-table-filters],
    [data-table-actions],
    [data-table-bulk-actions],
    [data-table-search],
    [data-table-toolbar],
    .filament-tables-container,
    .filament-tables-table,
    .filament-tables-header,
    .filament-tables-body,
    .filament-tables-footer,
    .filament-tables-empty-state,
    .filament-tables-pagination,
    .filament-tables-filters,
    .filament-tables-actions,
    .filament-tables-bulk-actions,
    .filament-tables-search,
    .filament-tables-toolbar {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
        opacity: 0 !important;
    }

    /* Ocultar cualquier div que contenga "No se encontraron registros" */
    div:contains("No se encontraron registros") {
        display: none !important;
    }

    /* Ocultar elementos con texto específico */
    [data-testid="table-empty-state"],
    [data-testid="table-container"],
    [data-testid="table-header"],
    [data-testid="table-body"],
    [data-testid="table-footer"] {
        display: none !important;
    }
</style>

<div class="flex flex-col space-y-4 mb-6">
    <!-- Título y botón de exportar -->
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Planilla Recaudador</h1>
        <button wire:click="exportToPDF" wire:loading.attr="disabled" wire:target="exportToPDF"
            class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
            <span wire:loading.remove wire:target="exportToPDF">Exportar a PDF</span>
            <span wire:loading wire:target="exportToPDF">Generando PDF...</span>
        </button>
    </div>

    <!-- Fila con filtros alineados a la izquierda -->
    <div class="flex items-start gap-4">
        <!-- Filtro de Orden de Ruta -->

        <!-- Filtro de Ruta -->
        <div class="w-48">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Ruta</label>
            <select wire:model="rutaId"
                class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @foreach($rutas as $ruta)
                <option value="{{ $ruta->id_ruta }}">{{ $ruta->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-48">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Orden de Ruta</label>
            <select wire:model="tableFilters.ordenar_por"
                class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="ruta">Orden de Ruta</option>
                <option value="fecha">Fecha</option>
                <option value="nombre">Nombre del Cliente</option>
            </select>
        </div>

        <!-- Filtro de Estado de Crédito -->
        <div class="w-48">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Estado Crédito</label>
            <select wire:model="estadoCredito"
                class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="todos">Todos</option>
                <option value="activos">Créditos Activos</option>
                <option value="cancelados">Créditos Cancelados</option>
                <option value="adicionales">Créditos Adicionales</option>
            </select>
        </div>
    </div>
</div>

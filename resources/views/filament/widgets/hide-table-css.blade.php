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
    .fi-ta-header-cell,
    .fi-ta-row,
    .fi-ta-cell,
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
    .fi-table-header-cell,
    .fi-table-row,
    .fi-table-cell,
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
    [data-table-header-cell],
    [data-table-row],
    [data-table-cell],
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

    /* Ocultar cualquier tabla que contenga "No se encontraron registros" */
    .fi-ta-content:has(.fi-ta-empty-state),
    .fi-table-container:has(.fi-table-empty-state),
    .filament-tables-container:has(.filament-tables-empty-state) {
        display: none !important;
    }

    /* Ocultar mensajes de estado vacío */
    .fi-ta-empty-state-heading,
    .fi-ta-empty-state-description,
    .fi-ta-empty-state-icon,
    .fi-table-empty-state-heading,
    .fi-table-empty-state-description,
    .fi-table-empty-state-icon,
    .filament-tables-empty-state-heading,
    .filament-tables-empty-state-description,
    .filament-tables-empty-state-icon {
        display: none !important;
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
<x-filament::page>
    <style>
    .report-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .report-table-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 1rem;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .report-table-content {
        overflow-x: auto;
    }

    .responsive-table {
        width: 100%;
        border-collapse: collapse;
    }

    .responsive-table th {
        background-color: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
    }

    .responsive-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }

    .responsive-table tr:hover {
        background-color: #f9fafb;
    }

    .amount-cell {
        font-weight: 600;
        color: #059669;
    }

    .route-cell {
        font-weight: 500;
        color: #1f2937;
    }

    .date-filter-dropdown {
        z-index: 50;
    }

    /* Responsive design for mobile */
    @media (max-width: 768px) {
        .report-table-header {
            padding: 12px 15px;
            font-size: 14px;
        }

        .report-table-content {
            padding: 15px;
        }

        .responsive-table {
            font-size: 13px;
        }

        .responsive-table th,
        .responsive-table td {
            padding: 8px 6px;
        }

        /* Hacer que las tablas sean scrollables horizontalmente en móvil */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Estilos específicos para celdas en móvil */
        .mobile-stack {
            display: block !important;
        }

        .mobile-stack>div {
            margin-bottom: 8px;
        }

        .mobile-stack td {
            display: block;
            text-align: right;
            border: none;
            padding: 4px 12px;
        }

        .mobile-stack td:before {
            content: attr(data-label) ": ";
            float: left;
            font-weight: bold;
            color: #374151;
        }

        .mobile-stack tr {
            border: 1px solid #e5e7eb;
            margin-bottom: 10px;
            border-radius: 8px;
            background: white;
        }

        /* Ajustar el filtro de fecha para móvil */
        .date-filter-mobile {
            flex-direction: column;
        }

        .date-filter-mobile .form-control {
            margin-bottom: 10px;
        }
    }

    /* Estilos adicionales para mejor visualización */
    .table-container {
        overflow-x: auto;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .report-table-content {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Eliminar márgenes de las tablas internas */
    .table-container .overflow-x-auto {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .table-container table {
        margin: 0 !important;
        width: 100% !important;
    }

    /* Espaciado entre tablas de reporte */
    .report-table {
        margin-bottom: 1.5rem !important;
    }

    .report-table:last-child {
        margin-bottom: 0 !important;
    }

    /* Responsive design for mobile */
    @media (max-width: 768px) {
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 !important;
            padding: 0 !important;
        }

        .report-table {
            margin-bottom: 1.5rem !important;
            padding: 0 !important;
        }

        .report-table:last-child {
            margin-bottom: 0 !important;
        }
    }

    .summary-row {
        background-color: #f3f4f6;
        font-weight: 600;
    }

    .highlight-cell {
        background-color: #fef3c7;
        font-weight: 600;
    }
    </style>

    <div class="space-y-6">
        <!-- Filtros de Fecha -->
        <div x-data="{ open: false }" class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <i class="fas fa-filter text-blue-600 mr-2"></i>
                        Filtros de Reportes
                    </h3>
                    <p class="text-sm text-gray-600">Selecciona el período y tipo de reporte a visualizar</p>
                </div>

                <!-- Filtro de Fecha -->
                <div class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md bg-white text-sm text-gray-900 hover:bg-gray-50 transition">
                        <i class="fas fa-calendar-alt text-gray-600"></i>
                        @php
                        \Carbon\Carbon::setLocale('es');
                        @endphp
                        {{ $this->fechaDesde ? \Carbon\Carbon::parse($this->fechaDesde)->translatedFormat('d M Y') : 'Desde' }}
                        -
                        {{ $this->fechaHasta ? \Carbon\Carbon::parse($this->fechaHasta)->translatedFormat('d M Y') : 'Hasta' }}
                        <i class="fas fa-chevron-down text-gray-600"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 p-4 space-y-4 date-filter-dropdown">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Período:</label>
                            <select wire:model="periodoSeleccionado"
                                class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-full">
                                <option value="hoy">Hoy</option>
                                <option value="ayer">Ayer</option>
                                <option value="esta_semana">Esta semana</option>
                                <option value="semana_pasada">Semana pasada</option>
                                <option value="este_mes">Este mes</option>
                                <option value="mes_pasado">Mes pasado</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rango personalizado:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" wire:model="fechaDesde"
                                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" />
                                <input type="date" wire:model="fechaHasta"
                                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2">
                            <button wire:click="limpiarFiltros" class="text-xs text-gray-500 hover:text-gray-700">
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de filtros adicionales -->
            <div class="mt-4">
                {{ $this->form }}
            </div>
        </div>

        <!-- Panel de Reportes Principal
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">
                <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                Panel de Reportes
            </h1>
            <p class="text-lg text-gray-600">Accede a todos los reportes financieros y estadísticas</p>
        </div>-->

        <!-- Todas las Tablas de Reportes -->
        <div class="space-y-6">
            @if(!$this->esAccesoPorRuta())
            <!-- Tabla 1: Cantidad Abonado -->
            <div class="report-table">
                <div class="report-table-header">
                    <i class="fas fa-coins mr-2"></i>
                    CANTIDAD ABONADO
                </div>
                <div class="report-table-content">
                    <div class="table-container">
                        @include('reportes-cristian.cantidad-abonado-content')
                    </div>
                </div>
            </div>

            <!-- Tabla 2: Total Yapeado del Día -->
            <div class="report-table">
                <div class="report-table-header">
                    <i class="fas fa-mobile-alt mr-2"></i>
                    TOTAL YAPEADO DEL DIA
                </div>
                <div class="report-table-content">
                    <div class="table-container">
                        @include('reportes-cristian.total-yapeado-content')
                    </div>
                </div>
            </div>

            <!-- Tabla 3: Préstamos Entregados -->
            <div class="report-table">
                <div class="report-table-header">
                    <i class="fas fa-hand-holding-usd mr-2"></i>
                    TOTAL PRESTAMOS ENTREGADOS
                </div>
                <div class="report-table-content">
                    <div class="table-container">
                        @include('reportes-cristian.prestamos-entregados-content')
                    </div>
                </div>
            </div>
            @endif

            <!-- Tabla 4: Reporte de Abonos -->
            <div class="report-table">
                <div class="report-table-header flex justify-between items-center">
                    <div>
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        REPORTE DE ABONOS
                    </div>
                    @if($this->esAdministrador())
                    <!--
                    <button wire:click="abrirModalRutas"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-cog"></i>
                        Configurar Rutas
                    </button>
                    -->
                    @endif
                </div>
                <div class="report-table-content">
                    <div class="table-container">
                        @include('reportes-cristian.reporte-abonos-content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Configuración de Rutas -->
    @if($mostrarModalRutas)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cerrarModalRutas"></div>

            <!-- Modal -->
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-cog text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Configurar Rutas para ReportesCristian
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-4">
                                    Selecciona las rutas que pueden acceder al módulo ReportesCristian:
                                </p>

                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @php
                                    $todasLasRutas = \App\Models\Ruta::where('activa', true)->get();
                                    @endphp

                                    @foreach($todasLasRutas as $ruta)
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="rutasSeleccionadas"
                                            value="{{ $ruta->nombre }}"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">{{ $ruta->nombre }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="guardarConfiguracionRutas" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar
                    </button>
                    <button wire:click="cerrarModalRutas" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
    .fas {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
    }
    </style>
</x-filament::page>

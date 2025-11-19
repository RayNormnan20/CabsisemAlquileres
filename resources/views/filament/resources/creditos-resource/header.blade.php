@php
$clienteIds = array_keys($clientes->toArray());
$currentIndex = array_search($clienteId, $clienteIds);
$anteriorId = $currentIndex > 0 ? $clienteIds[$currentIndex - 1] : null;
$siguienteId = isset($clienteIds[$currentIndex + 1]) ? $clienteIds[$currentIndex + 1] : null;
@endphp
{{-- SELECTOR DE CLIENTE CON NAVEGACIÓN Y FILTRO --}}
<div class="mb-6">
    <div class="flex items-center justify-center gap-4 mb-4">
        {{-- Botón anterior --}}
        <button wire:click="$set('clienteId', {{ $anteriorId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($anteriorId===null)>
            ◀
        </button>

        {{-- Selector con búsqueda en el centro --}}
        <div class="w-1/2 relative" x-data="{
            open: false,
            search: '',
            selectedClienteId: @entangle('clienteId'),
            get filteredClientes() {
                if (!this.search) return @js($clientes);
                const clientes = @js($clientes);
                const filtered = {};
                Object.keys(clientes).forEach(id => {
                    if (clientes[id].toLowerCase().includes(this.search.toLowerCase())) {
                        filtered[id] = clientes[id];
                    }
                });
                return filtered;
            },
            get selectedClienteName() {
                if (!this.selectedClienteId) return '-- Seleccionar --';
                const clientes = @js($clientes);
                return clientes[this.selectedClienteId] || 'Cliente no encontrado';
            },
            selectCliente(id, nombre) {
                this.selectedClienteId = id === '' ? null : parseInt(id);
                this.open = false;
                this.search = '';
            }
        }">
            <label for="clienteId"
                class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 text-center">
                Seleccionar Cliente
            </label>

            <!-- Input/Button principal -->
            <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-left text-sm text-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500">
                <span x-text="selectedClienteName" class="block truncate"></span>
                <svg class="w-4 h-4 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown -->
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-hidden">
                <!-- Input de búsqueda -->
                <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                    <input x-model="search" type="text" placeholder="Buscar cliente..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                </div>

                <!-- Lista de opciones -->
                <div class="max-h-48 overflow-y-auto">
                    <!-- Opción "Seleccionar" -->
                    <button @click="selectCliente('', '-- Seleccionar --')" type="button"
                        class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                        :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedClienteId === null }">
                        -- Seleccionar --
                    </button>

                    <!-- Opciones de clientes filtradas -->
                    <template x-for="[id, nombre] in Object.entries(filteredClientes)" :key="id">
                        <button @click="selectCliente(id, nombre)" type="button"
                            class="w-full px-3 py-2 text-left text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 focus:bg-gray-100 dark:focus:bg-gray-600 focus:outline-none"
                            :class="{ 'bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400': selectedClienteId == id }">
                            <span x-text="nombre"></span>
                        </button>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <div x-show="Object.keys(filteredClientes).length === 0 && search !== ''"
                        class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm">
                        No se encontraron clientes
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón siguiente --}}
        <button wire:click="$set('clienteId', {{ $siguienteId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($siguienteId===null)>
            ▶
        </button>
    </div>
</div>

{{-- FILTRO DE FECHAS Y EXPORTACIÓN --}}
<div class="flex flex-col sm:flex-row items-center gap-4 lg:gap-5 mb-6">
    <!-- Componente unificado de filtro de fechas -->
    <div class="relative inline-block text-left" x-data="{ open: false }">
        <!-- Botón desplegable -->
        <button @click="open = !open"
            class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600">
            <x-heroicon-o-calendar class="w-4 h-4 text-gray-600 dark:text-gray-300" />
            {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d M Y') : 'Desde' }}
            -
            {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d M Y') : 'Hasta' }}
            <svg class="w-4 h-4 ml-1 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            class="absolute z-50 mt-2 w-90 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 p-4 space-y-3">
            <!-- Selector de período -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Período:</label>
                <select wire:model="fechaPeriodo" wire:change="aplicarPeriodoFecha($event.target.value)"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="hoy">Hoy</option>
                    <option value="ayer">Ayer</option>
                    <option value="semana">Esta semana</option>
                    <option value="semana_anterior">Semana pasada</option>
                    <option value="ultimas_2_semanas">Últimas 2 semanas</option>
                    <option value="mes">Este mes</option>
                    <option value="mes_anterior">Mes pasado</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>

            <!-- Rango de fechas -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Rango personalizado:</label>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="fechaDesde"
                        class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm" />
                    <span class="text-gray-500 dark:text-gray-400">-</span>
                    <input type="date" wire:model="fechaHasta"
                        class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm" />
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex gap-2" style="display: flex !important; visibility: visible !important;">
        @if($clienteId)
        <button wire:click="resetearFiltros"
            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-md transition-colors">
            Limpiar
        </button>
        @endif
        <button wire:click="exportarPDF"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            {{ $clienteId ? 'PDF' : 'PDF' }}
        </button>
        <button wire:click="exportExcel"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            {{ $clienteId ? 'Excel' : 'Excel' }}
        </button>
        <button wire:click="{{ $clienteId ? 'verHistorialCliente' : '' }}"
            class="px-4 py-2 {{ $clienteId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }} text-white text-sm rounded-md transition-colors flex items-center gap-1"
            {{ !$clienteId ? 'disabled' : '' }}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Historial
        </button>
        
    </div>
</div>

@if($cliente)
@php
// Asegurarnos de cargar la relación creditos si no está cargada
$cliente->loadMissing('creditos');
@endphp

{{-- ¡IMPORTANTE! Se ha eliminado 'overflow-hidden' de este div --}}
<div class="bg-white rounded-lg shadow border border-gray-200 mb-6">
    {{-- Encabezado con nombre y botones --}}
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">{{ $cliente->nombre_completo }}</h2>

        <div x-data="{
            open: false,
            showDeactivationModal: false,
                    // Nuevos estados para cancelación con modal
                    showCancelModal: false,
                    showCancelResultModal: false,
                    cancelCreditoId: null,
                    cancelSaldoActual: 0,
                    cancelRedirectUrl: '',

                    // Modal de horarios removido, se gestiona en Configuración

                    deactivatingCreditId: null,
            isRenewal: {{ request()->query('accion') === 'renovacion' ? 'true' : 'false' }},
            mostrarRenovacionCompleta: false,
            isShowingRenovacionSteps: {{ request()->query('accion') === 'renovacion' ? 'true' : 'false' }},
            isShowingBajoCuentaSteps: false, // New variable to control bajo cuenta steps

                    // Missing variables that need to be added:
                    fechaActualEditable: '',
                    saldoOriginalSinDescuento: 0,
                    newNumeroCuotas: 1,

                    clientName: '',
                    capital: '',
                    interes: '',
                    saldo: '',
                    abonos: '',
                    saldoActual: '',

                    fechaInicio: '',
                    fechaVencimiento: '',

                    newValorCredito: '',
                    newInteres: '',
                    newFormaPago: '',
                    newCuenta: '',
                    newValorCuota: '',
                    newVencimientoDate: '',

                    // Nueva variable para el descuento
                    descuento: 0,

                    renovacion: 0,
                    montoTemporal: 0,
                    newFecha: '',
                    newFechaVencimiento: '',

                    // Campos directos para medios de pago
                    efectivo: 0,
                    yape: 0,
                    nombreYapeCliente: '',
                    opcionesYapeCliente: [],
                    caja: 0,
                    descuentoPago: 0,
                    abonoCompletarPrestamo: 0,
                    otro: 0,

                    // Mantener compatibilidad con el sistema existente
                    mediosDisponibles: ['Efectivo', 'Yape', 'Plin', 'Tarjeta', 'Transferencia'],
                    pagos: [],
                    medioSeleccionado: '',

                    get aEntregar() {
                        const resultado = this.renovacion - this.saldoActual;
                        return resultado > 0 ? resultado : 0;
                    },

                    get totalEntregado() {
                        return this.pagos.reduce((acc, pago) => acc + Number(pago.monto), 0);
                    },

                    get totalMediosPagoDirectos() {
                        return Number(this.efectivo || 0) +
                               Number(this.yape || 0) +
                               Number(this.caja || 0) +
                               Number(this.descuentoPago || 0) +
                               Number(this.abonoCompletarPrestamo || 0) +
                               Number(this.otro || 0);
                    },

                    get valorCredito() {
                        return this.aEntregar;
                    },

                    // Nueva función para calcular la nueva cuenta con descuento
                    calcularNuevaCuentaConDescuento() {
                        // Validar que el descuento no sea mayor al saldo actual
                        const saldoActualSinDescuento = parseFloat(this.saldoOriginalSinDescuento) || 0;
                        const descuentoAplicado = parseFloat(this.descuento) || 0;

                        if (descuentoAplicado > saldoActualSinDescuento) {
                            alert('El descuento no puede ser mayor al saldo actual');
                            this.descuento = 0;
                            return;
                        }

                        if (descuentoAplicado < 0) {
                            alert('El descuento no puede ser negativo');
                            this.descuento = 0;
                            return;
                        }

                        // NO modificamos this.saldoActual aquí
                        // El saldo actual se mantiene sin cambios para mostrar el saldo real

                        // Solo recalcular la nueva cuenta si hay interés y forma de pago
                        if (this.newInteres && this.newFormaPago) {
                            this.calcularFormaPagoYVencimiento();
                        }
                    },

                    agregarMedioPago() {
                        if (!this.medioSeleccionado || !this.montoTemporal) {
                            alert('Debe completar el tipo de pago y el monto.');
                            return;
                        }

                        const nuevoMonto = Number(this.montoTemporal);
                        const restante = this.valorCredito - this.totalEntregado;

                        if (nuevoMonto > restante) {
                            alert(`El monto excede el valor restante por entregar: S/ ${restante.toFixed(2)}`);
                            return;
                        }

                        this.pagos.push({
                            tipo: this.medioSeleccionado,
                            monto: nuevoMonto
                        });

                        this.medioSeleccionado = '';
                        this.montoTemporal = 0;
                    },

                    eliminarMedioPago(index) {
                        this.pagos.splice(index, 1);
                    },

                    get totalEntregado() {
                        return this.pagos.reduce((acc, pago) => acc + Number(pago.monto || 0), 0);
                    },

                    init() {
                        this.$watch('newInteres', () => this.calcularFormaPagoYVencimiento());
                        this.$watch('newFormaPago', () => this.calcularFormaPagoYVencimiento());

                        // Nuevo watcher para el descuento
                        this.$watch('descuento', () => this.calcularNuevaCuentaConDescuento());

                        // Nuevo watcher para la fecha actual editable
                        this.$watch('fechaActualEditable', () => {
                            this.calcularFechaVencimiento();
                            this.fechaInicio = this.fechaActualEditable;
                        });

                        // Watcher para yape - cargar opciones y seleccionar nombre automáticamente
                        this.$watch('yape', (value) => {
                            if (value > 0) {
                                this.cargarOpcionesYapeCliente();
                            } else {
                                this.nombreYapeCliente = '';
                                this.opcionesYapeCliente = [];
                            }
                        });

                        this.$watch(() => [this.newInteres, this.newFormaPago], ([interes, forma]) => {
                            this.mostrarRenovacionCompleta = interes && forma;
                        });

                        const hoy = new Date().toISOString().split('T')[0];
                        this.newFecha = hoy;

                        // Inicializar la fecha actual editable
                        this.fechaActualEditable = hoy;

                        this.$watch('newFormaPago', () => {
                            this.calcularFechaVencimiento();
                        });

                        this.$nextTick(() => {
                            this.$dispatch('input', { target: { value: this.newFormaPago } });
                        });
                    },

                    // Nueva función específica para calcular la fecha de vencimiento
                    calcularFechaVencimiento() {
                        const diasPago = parseInt(this.newFormaPago);

                        if (!diasPago || isNaN(diasPago) || diasPago <= 0) {
                            this.newVencimientoDate = '';
                            return;
                        }

                        // Usar la fecha actual editable como base
                        const fechaBase = new Date(this.fechaActualEditable || new Date().toISOString().split('T')[0]);
                        // Siempre usar 30 días (un mes) para el cálculo de fecha de vencimiento en renovaciones
                        fechaBase.setDate(fechaBase.getDate() + 30);
                        this.newVencimientoDate = fechaBase.toISOString().split('T')[0];
                    },

                    calcularDiasTranscurridos(fechaInicio) {
                        if (!fechaInicio) return 0;
                        const inicio = new Date(fechaInicio.replace(' ', 'T'));
                        // Usar la fecha actual editable en lugar de la fecha del sistema
                        const fechaActual = new Date(this.fechaActualEditable || new Date().toISOString().split('T')[0]);
                        const diffTime = fechaActual - inicio;
                        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    },

                    formatearFecha(fecha) {
                        if (!fecha) return '';
                        const date = new Date(fecha.replace(' ', 'T'));
                        return date.toISOString().split('T')[0];
                    },

                    setDeactivationCredit(creditId, creditData, isRenewal = false) {
                       // console.log('data', creditData)
                        this.deactivatingCreditId = creditId;

                        this.clientName = creditData.clientName;
                        this.capital = creditData.capital;

                        this.interes = (creditData.capital * (creditData.interes / 100)).toFixed(2);
                        this.saldo = (creditData.capital + parseFloat(this.interes)).toFixed(2);
                        this.fechaInicio = this.formatearFecha(creditData.fechaInicio);
                        this.fechaVencimiento = this.formatearFecha(creditData.fechaVencimiento);

                        // Inicializar la fecha actual editable con la fecha actual del sistema
                        this.fechaActualEditable = new Date().toISOString().split('T')[0];
                        this.renovacion = 0;
                        this.pagos = [];
                        this.medioSeleccionado = '';
                        this.montoTemporal = 0;


                        let totalAbonos = 0;
                        if (Array.isArray(creditData.abonos) && creditData.abonos.length > 0) {
                            totalAbonos = creditData.abonos.reduce(
                                (sum, abono) => sum + parseFloat(abono.monto_abono),
                                0
                            );
                            this.abonos = totalAbonos.toFixed(2);
                        } else {
                            this.abonos = 'No hay abonos por el momento';
                            totalAbonos = 0;
                        }

                        // USAR DIRECTAMENTE EL SALDO QUE VIENE DE LA BASE DE DATOS
                        // En lugar de calcularlo, usar el saldo real del crédito
                        const saldoReal = parseFloat(creditData.saldo || 0).toFixed(2);

                        // Guardar el saldo original SIN descuento para los cálculos
                        this.saldoOriginalSinDescuento = saldoReal;

                        // MODIFICACIÓN: Para renovaciones, mostrar el valor de Nueva Cuenta en Saldo Actual
                        if (isRenewal) {
                            // En renovaciones, el Saldo Actual debe mostrar el valor de Nueva Cuenta
                            // Inicialmente será el saldo real, pero se actualizará cuando se calcule la nueva cuenta
                            this.saldoActual = saldoReal;
                        } else {
                            // Para actualizaciones normales, mostrar el saldo real
                            this.saldoActual = saldoReal;
                        }

                        this.newValorCredito = creditData.capital;
                        this.newInteres = creditData.interes;
                        this.newFormaPago = '';
                        this.newCuenta = '';
                        this.newValorCuota = '';
                        this.newVencimientoDate = creditData.fechaVencimiento || '';

                        // Resetear el descuento
                        this.descuento = 0;

                        // Cargar nombre Yape existente si hay uno asociado al crédito
                        if (isRenewal) {
                            this.cargarNombreYapeExistente(creditId);
                        }

                        this.isRenewal = isRenewal;
                        this.showDeactivationModal = true;
                        this.open = false;
                        this.isShowingRenovacionSteps = false; // Reset to step 1
                    },

                    calcularFormaPagoYVencimiento() {
                        const capitalOriginal = parseFloat(this.capital);
                        const saldoActual = parseFloat(this.saldoOriginalSinDescuento); // Usar saldo original sin descuento
                        const interes = parseFloat(this.newInteres);
                        const diasPago = parseInt(this.newFormaPago);
                        const descuento = parseFloat(this.descuento) || 0;

                        if (isNaN(capitalOriginal) || isNaN(interes) || isNaN(diasPago) || diasPago <= 0) {
                            this.newCuenta = '';
                            this.newValorCuota = '';
                            this.newVencimientoDate = '';
                            return;
                        }

                        // Calcular el interés solo sobre los días ingresados en Forma de Pago basado en el capital original
                        const mesesExactos = diasPago / 30;
                        const interesMensual = capitalOriginal * (interes / 100);
                        const interesCalculado = interesMensual * mesesExactos;

                        // Tanto para renovaciones como para bajo cuenta: usar saldo actual + interés
                        // El interés siempre se calcula sobre el capital original
                        const totalSinDescuento = saldoActual + interesCalculado;

                        // Aplicar descuento al total final
                        const totalPagar = totalSinDescuento - descuento;
                        const cuotaDiaria = totalPagar / diasPago;
                        this.newValorCuota = cuotaDiaria.toFixed(2);
                        this.newCuenta = totalPagar.toFixed(2);

                        // Si es renovación, actualizar el Saldo Actual con el valor de Nueva Cuenta
                        if (this.isRenewal) {
                            this.saldoActual = this.newCuenta;
                        }

                        // Calcular la fecha de vencimiento usando la función específica
                        this.calcularFechaVencimiento();
                    },

                    guardarDatosCredito() {
                        fetch('/creditos/actualizar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                credito_id: this.deactivatingCreditId,
                                nuevo_interes: parseFloat(this.newInteres),
                                forma_pago: parseInt(this.newFormaPago),
                                nueva_cuenta: parseFloat(this.newCuenta),
                                valor_cuota: parseFloat(this.newValorCuota),
                                fecha_vencimiento: this.newVencimientoDate,
                                tipo_operacion: 'edicion' // Identificar como edición
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.message) {
                                alert(data.message);
                                this.showDeactivationModal = false;
                            } else {
                                alert(data.error || 'Error al actualizar');
                            }
                        })
                        .catch(e => {
                            console.error(e);
                            alert('Error de conexión.');
                        });
                    },

                    limpiarMediosPago() {
                        this.efectivo = 0;
                        this.yape = 0;
                        this.nombreYapeCliente = '';
                        this.caja = 0;
                        this.descuentoPago = 0;
                        this.abonoCompletarPrestamo = 0;
                        this.otro = 0;
                    },

                    confirmDeactivation() {
                        if (this.isRenewal) {
                            // Validar que si hay Yape, el nombre del cliente sea requerido
                            if (parseFloat(this.yape || 0) > 0 && !this.nombreYapeCliente) {
                                alert('El nombre del cliente Yape es requerido cuando se ingresa un monto en Yape.');
                                return;
                            }

                            // Validar que la suma de medios de pago sea igual al monto a entregar
                            const totalMediosPago = parseFloat(this.efectivo || 0) +
                                                  parseFloat(this.yape || 0) +
                                                  parseFloat(this.caja || 0) +
                                                  parseFloat(this.descuentoPago || 0) +
                                                  parseFloat(this.abonoCompletarPrestamo || 0) +
                                                  parseFloat(this.otro || 0);

                            const montoAEntregar = parseFloat(this.aEntregar || 0);

                            if (totalMediosPago !== montoAEntregar) {
                                alert(`La suma de los medios de pago (S/ ${totalMediosPago.toFixed(2)}) debe ser igual al monto a entregar (S/ ${montoAEntregar.toFixed(2)}). Por favor, complete los medios de pago correctamente.`);
                                return;
                            }

                            // Armar payload
                            const payload = {
                                id: this.deactivatingCreditId,
                                valor_credito: parseFloat(this.renovacion),
                                porcentaje_interes: parseFloat(this.newInteres),
                                dias_plazo: parseInt(this.newFormaPago),
                                fecha_vencimiento: this.newVencimientoDate,
                                fecha_credito: this.fechaActualEditable, // Agregar fecha editada
                                valor_cuota: parseFloat(this.newValorCuota),
                                numero_cuotas: parseInt(this.newNumeroCuotas) || 1,
                                descuento: parseFloat(this.descuento) || 0, // Agregar descuento
                                medios_pago: [
                                    ...(this.efectivo > 0 ? [{ tipo: 'Efectivo', monto: parseFloat(this.efectivo) }] : []),
                                    ...(this.yape > 0 ? [{ tipo: 'Yape', monto: parseFloat(this.yape), nombre_cliente: this.nombreYapeCliente }] : []),
                                    ...(this.caja > 0 ? [{ tipo: 'Caja', monto: parseFloat(this.caja) }] : []),
                                    ...(this.descuentoPago > 0 ? [{ tipo: 'Descuento', monto: parseFloat(this.descuentoPago) }] : []),
                                    ...(this.abonoCompletarPrestamo > 0 ? [{ tipo: 'Abono para completar préstamo', monto: parseFloat(this.abonoCompletarPrestamo) }] : []),
                                    ...(this.otro > 0 ? [{ tipo: 'Otro', monto: parseFloat(this.otro) }] : [])
                                ]
                            };

                            fetch(`/creditos/renovar`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.message) {
                                    alert(data.message);
                                    console.log(data);
                                    this.limpiarMediosPago();
                                    this.showDeactivationModal = false;
                                    location.reload(); // Recargar para ver los cambios
                                } else {
                                    alert(data.error || 'Error al renovar');
                                }
                            })
                            .catch(e => {
                                console.error(e);
                                alert('Error de conexión.');
                            });
                        } else {
                            // Lógica para baja de cuenta
                            let payload = {
                                credito_id: this.deactivatingCreditId,
                                nuevo_interes: parseFloat(this.newInteres),
                                forma_pago: parseInt(this.newFormaPago),
                                nueva_cuenta: parseFloat(this.newCuenta),
                                valor_cuota: parseFloat(this.newValorCuota),
                                fecha_vencimiento: this.newVencimientoDate,
                                fecha_credito: this.fechaActualEditable, // Agregar fecha editada
                                descuento: parseFloat(this.descuento) || 0, // Agregar descuento
                                tipo_operacion: 'bajo_cuenta' // Identificar como bajo cuenta
                            };

                            // Si estamos en el paso de métodos de pago, incluir los medios de pago
                            if (this.isShowingBajoCuentaSteps) {
                                // Validar que si hay Yape, el nombre del cliente sea requerido
                                if (parseFloat(this.yape || 0) > 0 && !this.nombreYapeCliente) {
                                    alert('El nombre del cliente Yape es requerido cuando se ingresa un monto en Yape.');
                                    return;
                                }

                                // Validar que la suma de medios de pago sea igual al monto de nueva cuenta
                                const totalMediosPago = parseFloat(this.efectivo || 0) +
                                                      parseFloat(this.yape || 0) +
                                                      parseFloat(this.caja || 0) +
                                                      parseFloat(this.descuentoPago || 0) +
                                                      parseFloat(this.abonoCompletarPrestamo || 0) +
                                                      parseFloat(this.otro || 0);

                                const montoNuevaCuenta = parseFloat(this.newCuenta || 0);

                                if (totalMediosPago !== montoNuevaCuenta) {
                                    alert(`La suma de los medios de pago (S/ ${totalMediosPago.toFixed(2)}) debe ser igual al monto de nueva cuenta (S/ ${montoNuevaCuenta.toFixed(2)}). Por favor, complete los medios de pago correctamente.`);
                                    return;
                                }

                                // Agregar medios de pago al payload
                                payload.medios_pago = [
                                    ...(this.efectivo > 0 ? [{ tipo: 'Efectivo', monto: parseFloat(this.efectivo) }] : []),
                                    ...(this.yape > 0 ? [{ tipo: 'Yape', monto: parseFloat(this.yape), nombre_cliente: this.nombreYapeCliente }] : []),
                                    ...(this.caja > 0 ? [{ tipo: 'Caja', monto: parseFloat(this.caja) }] : []),
                                    ...(this.descuentoPago > 0 ? [{ tipo: 'Descuento', monto: parseFloat(this.descuentoPago) }] : []),
                                    ...(this.abonoCompletarPrestamo > 0 ? [{ tipo: 'Abono para completar préstamo', monto: parseFloat(this.abonoCompletarPrestamo) }] : []),
                                    ...(this.otro > 0 ? [{ tipo: 'Otro', monto: parseFloat(this.otro) }] : [])
                                ];
                            }

                            fetch('/creditos/actualizar', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.message) {
                                    alert(data.message);
                                    this.limpiarMediosPago();
                                    this.showDeactivationModal = false;
                                    location.reload(); // Recargar para ver los cambios
                                } else {
                                    alert(data.error || 'Error al actualizar');
                                }
                            })
                            .catch(e => {
                                console.error(e);
                                alert('Error de conexión.');
                            });
                        }
                    },

                    cargarNombreYapeExistente(creditId) {
                        // Hacer consulta al backend para obtener el YapeCliente asociado al crédito
                        fetch(`/creditos/${creditId}/yape-cliente`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.nombre_yape) {
                                this.nombreYapeCliente = data.nombre_yape;
                            } else {
                                // Si no hay nombre Yape registrado, usar el nombre del cliente por defecto
                                this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                            }
                        })
                        .catch(error => {
                            console.log('No hay YapeCliente asociado o error:', error);
                            // Si hay error, usar el nombre del cliente por defecto
                            this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                        });
                    },

                    cargarNombreYapeCompleto() {
                        // Hacer consulta al backend para obtener YapeCliente con saldo pendiente del mismo cliente
                        const clienteId = {{ $cliente->id_cliente }};
                        fetch(`/clientes/${clienteId}/yape-cliente-completo`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.nombre_yape) {
                                this.nombreYapeCliente = data.nombre_yape;
                            } else {
                                // Si no hay nombre Yape con saldo pendiente, usar el nombre del cliente por defecto
                                this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                            }
                        })
                        .catch(error => {
                            console.log('No hay YapeCliente con saldo pendiente o error:', error);
                            // Si hay error, usar el nombre del cliente por defecto
                            this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                        });
                    },

                    cargarOpcionesYapeCliente() {
                        // Cargar todas las opciones de YapeCliente para el cliente actual
                        const clienteId = {{ $cliente->id_cliente }};
                        fetch(`/clientes/${clienteId}/yape-clientes`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.yape_clientes) {
                                this.opcionesYapeCliente = data.yape_clientes;
                                // Si hay opciones y no hay nombre seleccionado, seleccionar el primero con saldo pendiente
                                if (this.opcionesYapeCliente.length > 0 && !this.nombreYapeCliente) {
                                    const pendiente = this.opcionesYapeCliente.find(yc => yc.saldo_pendiente > 0);
                                    this.nombreYapeCliente = pendiente ? pendiente.nombre_yape : this.opcionesYapeCliente[0].nombre_yape;
                                }
                            } else {
                                this.opcionesYapeCliente = [];
                                this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                            }
                        })
                        .catch(error => {
                            console.log('Error al cargar opciones YapeCliente:', error);
                            this.opcionesYapeCliente = [];
                            this.nombreYapeCliente = '{{ $cliente->nombre_completo }}';
                        });
                    },

                    confirmCancellation() {
                        @php
                            $creditoActivo = $cliente->creditoActivo();
                        @endphp

                        @if($creditoActivo)
                            const saldoActual = {{ $creditoActivo->saldo_actual ?? 0 }};
                            const creditoId = {{ $creditoActivo->id_credito ?? 'null' }};

                            if (!creditoId) {
                                alert('Error: No se pudo obtener el ID del crédito.');
                                return;
                            }

                            // Mostrar modal de confirmación con saldo actual
                            this.cancelCreditoId = creditoId;
                            this.cancelSaldoActual = saldoActual;
                            this.cancelRedirectUrl = `{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}`;
                            this.showCancelModal = true;
                        @else
                            alert('No hay crédito activo para cancelar.');
                        @endif
                    },

                    performCancellation() {
                        if (!this.cancelCreditoId) {
                            alert('Error: Falta el ID del crédito.');
                            return;
                        }
                        fetch('/creditos/cancelar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            },
                            body: JSON.stringify({ credito_id: this.cancelCreditoId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Cerrar modal de confirmación y mostrar resultado con saldo 0
                                this.showCancelModal = false;
                                this.cancelSaldoActual = 0;
                                this.showCancelResultModal = true;
                                // Guardar posible URL de redirección
                                this.cancelRedirectUrl = data.redirect_url || this.cancelRedirectUrl;
                            } else {
                                alert(data.error || 'Error al cancelar el crédito.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error de conexión al cancelar el crédito.');
                        });
                    },

                    renewCredit() {
                        const url = this.cancelRedirectUrl || `{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}`;
                        window.location.href = url;
                    },

                    // saveAccessHours removido. Gestión de horarios en Configuración.

                    // Funciones para el modal de renovación
                    showRenovacionSteps() {
                        this.isShowingRenovacionSteps = true;
                        this.cargarOpcionesYapeCliente();
                    },

                    hideRenovacionSteps() {
                        this.isShowingRenovacionSteps = false;
                    }

                }" x-init="init()" class="flex items-center space-x-2">
            {{-- Botón Editar Cliente protegido por policy --}}
            @can('update', $cliente)
            <a href="{{ route('filament.resources.clientes.edit', ['record' => $cliente->id_cliente]) }}"
                class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Editar Cliente
            </a>
            @endcan

            {{-- Configurar Horario movido a Configuración > Horarios de acceso --}}

            @if($cliente->creditos->isNotEmpty())
            {{-- Grupo para el botón Editar Crédito y el Dropdown de Acciones --}}
            <div class="flex items-center space-x-2">
                @php
                $creditoActivo = $cliente->creditoActivo();
                @endphp

                @if($creditoActivo)
                @can('update', $creditoActivo)
                <a href="{{ route('filament.resources.creditos.edit', ['record' => $creditoActivo->id_credito]) }}"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Editar Crédito
                </a>
                @endcan

                <div class="relative inline-block text-left z-20">
                    <div>
                        <button type="button" @click="open = !open"
                            class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            id="menu-button" aria-expanded="true" aria-haspopup="true">
                            Acciones
                            <svg class="-mr-1 ml-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-30"
                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none">
                            @if($cliente->creditos->isEmpty() || $cliente->creditos->every(fn($credito) =>
                            $credito->saldo_actual <= 0)) <a
                                href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}"
                                class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem"
                                tabindex="-1" id="menu-item-0">
                                Nuevo Préstamo
                                </a>
                                @endif

                                @if(request()->query('accion') !== 'renovacion')
                                <a href="#" @click.prevent="setDeactivationCredit(
                                        {{ $creditoActivo->id_credito }},
                                        {
                                            clientName: '{{ $cliente->nombre_completo ?? $cliente->nombre }}', // Ajusta según el campo de nombre de tu cliente
                                            capital: {{ $creditoActivo->valor_credito ?? 0 }},
                                            interes: {{ $creditoActivo->porcentaje_interes ?? 0 }},
                                            saldo: {{ $creditoActivo->saldo_actual ?? 0 }},
                                            abonos: {{ $creditoActivo->abonos ?? 0 }},
                                            fechaInicio: '{{ $creditoActivo->fecha_credito ?? '' }}', // <- FECHA DE INICIO
                                            fechaVencimiento: '{{ now()->format('Y-m-d') }}' // Fecha actual
                                        },
                                        false
                                    )" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem"
                                    tabindex="-1" id="menu-item-1">
                                    Bajo Cuenta
                                </a>
                                <a href="#" @click.prevent="confirmCancellation()"
                                    class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem"
                                    tabindex="-1" id="menu-item-2">Cancelado</a>
                                @endif
                                <a href="#" @click.prevent="setDeactivationCredit(
                                            {{ $creditoActivo->id_credito }},
                                            {
                                                clientName: '{{ $cliente->nombre_completo ?? $cliente->nombre }}',
                                                capital: {{ $creditoActivo->valor_credito ?? 0 }},
                                                interes: {{ $creditoActivo->porcentaje_interes ?? 0 }},
                                                saldo: {{ $creditoActivo->saldo_actual ?? 0 }},
                                                abonos: {{ $creditoActivo->abonos ?? 0 }},
                                                saldoActual: {{ $creditoActivo->saldo_actual ?? 0 }},
                                                fechaInicio: '{{ $creditoActivo->fecha_credito ?? '' }}',
                                                fechaVencimiento: '{{ $creditoActivo->fecha_vencimiento ?? '' }}',
                                            },
                                            true
                                    )" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">
                                    Renovación
                                </a>

                        </div>
                    </div>
                </div>
                @else
                {{-- Si no hay crédito activo, mostrar botón de crear y adicional --}}
                <a href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Crear Crédito
                </a>
                <a href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente, 'tipo' => 'adicional']) }}"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Adicional
                </a>
                @endif
            </div>
            @else
            <a href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}"
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Crear Crédito
            </a>
            {{-- Botón Adicional - Mostrar cuando no hay crédito activo --}}
            @if(!$creditoActivo)
            <a href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente, 'tipo' => 'adicional']) }}"
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Adicional
            </a>
            @endif

            @endif

            {{-- Modal de Confirmación para Baja de Cuenta --}}
            <div x-show="showDeactivationModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    {{-- Fondo del overlay --}}
                    <div x-show="showDeactivationModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"
                        @click="showDeactivationModal = false"></div>

                    {{-- Este span es para centrar el contenido del modal horizontalmente --}}
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    {{-- Panel del modal --}}
                    <div x-show="showDeactivationModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        {{-- Aumentado el ancho a sm:max-w-3xl --}}
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m-9.303 3.376c-.866 1.26-3.168 1.26-4.034 0L.436 4.673A1.875 1.875 0 012.007 2.25h14.536a1.875 1.875 0 011.571 2.423L12 9v3.75m-9.303 3.376c-.866 1.26-3.168 1.26-4.034 0L.436 4.673A1.875 1.875 0 012.007 2.25h14.536a1.875 1.875 0 011.571 2.423L12 9v3.75M10.125 15.75L12 21.75l-1.875-6zm-.825-4.725L12 11.25m0 0l-1.875-6z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h2 class="text-lg font-semibold text-gray-900"
                                        x-text="isRenewal ? 'Renovación de Crédito' : 'Bajo Cuenta'">
                                    </h2>
                                    <div class="mt-4 p-4 border border-gray-300 rounded-lg bg-blue-50">
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center font-semibold text-gray-800">
                                            <div>
                                                <span class="block text-sm text-gray-600">Fecha de Inicio</span>
                                                <span x-text="fechaInicio" class="text-lg"></span>
                                            </div>
                                            <div>
                                                <span class="block text-sm text-gray-600">Fecha Actual</span>
                                                <span x-text="new Date().toISOString().split('T')[0]"
                                                    class="text-lg"></span>
                                            </div>
                                            <div>
                                                <span class="block text-sm text-gray-600">Días Transcurridos</span>
                                                <span x-text="calcularDiasTranscurridos(fechaInicio)"
                                                    class="text-lg text-indigo-700"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="(!isRenewal && !isShowingBajoCuentaSteps) || (isRenewal && !isShowingRenovacionSteps)"
                                        class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Columna Izquierda: Datos del Crédito (Solo lectura) --}}
                                        <div>
                                            <h4 class="text-md font-semibold text-gray-800 mb-2">Datos Actuales</h4>

                                            <div class="mb-3">
                                                <label for="cliente-display"
                                                    class="block text-sm font-medium text-gray-700">Cliente</label>
                                                <input type="text" id="cliente-display" x-model="clientName" disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="capital-display"
                                                    class="block text-sm font-medium text-gray-700">Capital</label>
                                                <input type="text" id="capital-display" x-model="capital" disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="interes-display"
                                                    class="block text-sm font-medium text-gray-700">Interés</label>
                                                <input type="text" id="interes-display" x-model="interes" disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="saldo-display"
                                                    class="block text-sm font-medium text-gray-700">Saldo</label>
                                                <input type="text" id="saldo-display" x-model="saldo" disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="abonos-display"
                                                    class="block text-sm font-medium text-gray-700">Abonos</label>
                                                <template x-if="abonos === 'No hay abonos por el momento'">
                                                    <input type="text" value="No hay abonos por el momento" disabled
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-red-600 italic" />
                                                </template>
                                                <template x-if="abonos !== 'No hay abonos por el momento'">
                                                    <input type="text" x-model="abonos" disabled
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600" />
                                                </template>
                                            </div>

                                            <div class="mb-3">
                                                <label for="saldo-actual-display"
                                                    class="block text-sm font-medium text-gray-700">Saldo Actual</label>
                                                <input type="text" id="saldo-actual-display" x-model="saldoActual"
                                                    disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                        </div>

                                        {{-- Columna Derecha: Campos a Editar --}}
                                        <div>
                                            <h4 class="text-md font-semibold text-gray-800 mb-2">Nuevo Cálculo</h4>

                                            <div class="mb-3">
                                                <label for="valor-credito"
                                                    class="block text-sm font-medium text-gray-700">Valor
                                                    Crédito</label>
                                                <input type="number" id="valor-credito" x-model="newValorCredito"
                                                    disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>

                                            <!-- Nuevo campo de Descuento -->
                                            <div class="mb-3">
                                                <label for="descuento"
                                                    class="block text-sm font-medium text-gray-700">Descuento</label>
                                                <input type="number" step="0.01" id="descuento" x-model="descuento"
                                                    placeholder="Ingrese el descuento"
                                                    class="mt-1 block w-full rounded-md border-blue-400 shadow-sm bg-blue-50 focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50">
                                                <!-- <p class="mt-1 text-xs text-gray-500">El descuento se restará del saldo actual para calcular la nueva cuenta</p> -->
                                            </div>

                                            <div class="mb-3">
                                                <label for="nuevo-interes"
                                                    class="block text-sm font-medium text-gray-700">Nuevo
                                                    Interés</label>
                                                <input type="number" step="0.01" id="nuevo-interes" x-model="newInteres"
                                                    @input="calcularFormaPagoYVencimiento()"
                                                    class="mt-1 block w-full rounded-md border-blue-400 shadow-sm bg-blue-50 focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="forma-pago"
                                                    class="block text-sm font-medium text-blue-700">Forma de Pago
                                                    (Días)</label>
                                                <input type="number" step="0.01" id="nuevo-forma-pago"
                                                    x-model="newFormaPago" @input="calcularFormaPagoYVencimiento()"
                                                    class="mt-1 block w-full rounded-md border-blue-400 shadow-sm bg-blue-50 focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="nueva-cuenta"
                                                    class="block text-sm font-medium text-gray-700">Nueva Cuenta</label>
                                                <input type="text" id="nueva-cuenta" x-model="newCuenta" disabled
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha-actual"
                                                    class="block text-sm font-medium text-gray-700">Fecha Actual</label>
                                                <input type="date" id="fecha-actual" x-model="fechaActualEditable"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                            <div class="mb-3">
                                                <label for="nueva-fecha-vencimiento"
                                                    class="block text-sm font-medium text-gray-700">Nueva Fecha de
                                                    Vencimiento</label>
                                                <input type="date" id="nueva-fecha-vencimiento"
                                                    x-model="newVencimientoDate" disabled readonly
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de métodos de pago para Bajo Cuenta -->
                                    <div x-show="!isRenewal && isShowingBajoCuentaSteps" class="mt-4">
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Métodos de Pago - Bajo Cuenta
                                        </h4>

                                        <!-- Mostrar el valor de Nueva Cuenta -->
                                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-blue-700">Nueva Cuenta:</span>
                                                <span class="text-lg font-bold text-blue-900"
                                                    x-text="'S/ ' + parseFloat(newCuenta || 0).toFixed(2)"></span>
                                            </div>
                                            <p class="text-xs text-blue-600 mt-1">La suma de los medios de pago debe ser
                                                igual a este monto</p>
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-3">Medios de
                                                Pago</label>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Efectivo</label>
                                                    <input type="number" x-model="efectivo" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Yape</label>
                                                    <input type="number" x-model="yape" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Caja</label>
                                                    <input type="number" x-model="caja" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Descuento</label>
                                                    <input type="number" x-model="descuentoPago" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Abono para
                                                        completar préstamo</label>
                                                    <input type="number" x-model="abonoCompletarPrestamo" min="0"
                                                        step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-blue-700">Otro</label>
                                                    <input type="number" x-model="otro" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <label class="block text-sm font-medium text-gray-700">Total Medios de
                                                    Pago</label>
                                                <input type="number" :value="totalMediosPagoDirectos.toFixed(2)"
                                                    readonly
                                                    class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                            </div>

                                            <div x-show="yape > 0" x-transition class="mt-4">
                                                <label class="block text-sm font-medium text-gray-700">Nombre Cliente
                                                    Yape <span class="text-red-500">*</span></label>
                                                <select x-model="nombreYapeCliente"
                                                    :class="!nombreYapeCliente && yape > 0 ? 'mt-1 block w-full border-red-400 bg-red-50 rounded-md focus:border-red-500 focus:ring focus:ring-red-300 focus:ring-opacity-50' : 'mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50'">
                                                    <option value="">Seleccione un nombre Yape</option>
                                                    <template x-for="opcion in opcionesYapeCliente" :key="opcion.id">
                                                        <option :value="opcion.nombre_yape"
                                                            :class="opcion.tiene_saldo_pendiente ? 'text-green-600 font-semibold' : 'text-gray-600'"
                                                            x-text="opcion.nombre_yape"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="isRenewal && isShowingRenovacionSteps" class="mt-4">
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Datos de Renovación</h4>
                                        <!-- Organizar en 2 columnas -->
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Saldo
                                                    Actual</label>
                                                <input type="number" x-model="saldoActual"
                                                    class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md"
                                                    readonly disabled />
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-blue-700">Renovación</label>
                                                <input type="number" x-model="renovacion"
                                                    class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50" />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">A
                                                    entregar</label>
                                                <input type="number" :value="aEntregar.toFixed(2)" readonly
                                                    class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                            </div>
                                            <div x-show="yape > 0" x-transition>
                                                <label class="block text-sm font-medium text-gray-700">Nombre Cliente
                                                    Yape <span class="text-red-500">*</span></label>
                                                <select x-model="nombreYapeCliente"
                                                    :class="!nombreYapeCliente && yape > 0 ? 'mt-1 block w-full border-red-400 bg-red-50 rounded-md focus:border-red-500 focus:ring focus:ring-red-300 focus:ring-opacity-50' : 'mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50'">
                                                    <option value="">Seleccione un nombre Yape</option>
                                                    <template x-for="opcion in opcionesYapeCliente" :key="opcion.id">
                                                        <option :value="opcion.nombre_yape"
                                                            :class="opcion.tiene_saldo_pendiente ? 'text-green-600 font-semibold' : 'text-gray-600'"
                                                            x-text="opcion.nombre_yape"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            {{-- <div>
                                                <label class="block text-sm font-medium text-gray-700">Valor del
                                                    Crédito</label>
                                                <input type="number" :value="valorCredito.toFixed(2)" readonly
                                                    class="mt-1 block w-full border-gray-300 rounded-md" />
                                            </div> --}}
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-3">Medios de
                                                Pago</label>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Efectivo</label>

                                                    <input type="number" x-model="efectivo" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Yape</label>
                                                    <input type="number" x-model="yape" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>



                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Caja</label>
                                                    <input type="number" x-model="caja" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Descuento</label>
                                                    <input type="number" x-model="descuentoPago" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Abono para
                                                        completar préstamo</label>
                                                    <input type="number" x-model="abonoCompletarPrestamo" min="0"
                                                        step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-blue-700">Otro</label>
                                                    <input type="number" x-model="otro" min="0" step="0.01"
                                                        class="mt-1 block w-full border-blue-400 bg-blue-50 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50"
                                                        placeholder="0.00" />
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <label class="block text-sm font-medium text-gray-700">Total Medios de
                                                    Pago</label>
                                                <input type="number" :value="totalMediosPagoDirectos.toFixed(2)"
                                                    readonly
                                                    class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="confirmDeactivation()"
                                x-show="(isRenewal && isShowingRenovacionSteps) || (!isRenewal && isShowingBajoCuentaSteps)"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                x-text="isRenewal ? 'Confirmar Renovación' : 'Confirmar Baja de Cuenta'">
                            </button>

                            <button type="button" @click="showRenovacionSteps()"
                                x-show="isRenewal && !isShowingRenovacionSteps && mostrarRenovacionCompleta"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Siguiente
                            </button>

                            <button type="button"
                                @click="isShowingBajoCuentaSteps = true; cargarOpcionesYapeCliente(); cargarNombreYapeCompleto()"
                                x-show="!isRenewal && !isShowingBajoCuentaSteps"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Siguiente
                            </button>

                            <button type="button" @click="hideRenovacionSteps()"
                                x-show="isRenewal && isShowingRenovacionSteps"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Volver
                            </button>

                            <button type="button" @click="isShowingBajoCuentaSteps = false"
                                x-show="!isRenewal && isShowingBajoCuentaSteps"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Volver
                            </button>

                            <button type="button" @click="showDeactivationModal = false"
                                x-show="!isShowingRenovacionSteps && !isShowingBajoCuentaSteps"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Confirmar Cancelación --}}
            <div x-show="showCancelModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showCancelModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        aria-hidden="true" @click="showCancelModal = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showCancelModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.26-3.168 1.26-4.034 0L.436 4.673A1.875 1.875 0 012.007 2.25h14.536a1.875 1.875 0 011.571 2.423L12 9v3.75M10.125 15.75L12 21.75l-1.875-6zm-.825-4.725L12 11.25m0 0l-1.875-6z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h2 class="text-lg font-semibold text-gray-900">Cancelar Crédito</h2>
                                    <p class="mt-2 text-sm text-gray-700">Saldo actual: <span class="font-bold">S/ <span x-text="Number(cancelSaldoActual).toFixed(2)"></span></span></p>
                                    <p class="mt-1 text-sm text-gray-600">¿Está seguro de que desea cancelar este crédito?</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="performCancellation()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-white text-base font-medium hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Confirmar
                            </button>
                            <button type="button" @click="showCancelModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal: Resultado de Cancelación y Renovación --}}
            <div x-show="showCancelResultModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showCancelResultModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true" @click="showCancelResultModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showCancelResultModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Abono realizado!</h2>
                            <p class="mt-2 text-sm text-gray-700">Saldo: <span class="font-bold">S/ <span x-text="Number(cancelSaldoActual).toFixed(2)"></span></span></p>
                            <p class="mt-2 text-sm text-gray-700">¿Desea renovar el crédito?</p>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="renewCredit()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-white text-base font-medium hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Aceptar
                            </button>
                            <button type="button" @click="showCancelResultModal = false; location.reload();"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal de horarios removido --}}
        </div>

    </div>

    {{-- Información desplegable --}}
    <div x-data="{ open: false }" class="px-6 py-4">
        <button @click="open = !open" type="button"
            class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium flex items-center focus:outline-none">
            <span x-text="open ? '▲ Ocultar información' : '▼ Ver información del cliente'"></span>
        </button>

        <div x-show="open" x-transition
            class="mt-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="text-gray-900 dark:text-gray-100"><span
                        class="font-medium text-gray-700 dark:text-gray-200">Documento:</span>
                    {{ $cliente->numero_documento }}</div>
                <div class="text-gray-900 dark:text-gray-100"><span
                        class="font-medium text-gray-700 dark:text-gray-200">Celular:</span> {{ $cliente->celular }}
                </div>
                <div class="text-gray-900 dark:text-gray-100"><span
                        class="font-medium text-gray-700 dark:text-gray-200">Teléfono:</span> {{ $cliente->telefono }}
                </div>
                <div class="text-gray-900 dark:text-gray-100"><span
                        class="font-medium text-gray-700 dark:text-gray-200">Dirección:</span> {{ $cliente->direccion }}
                </div>
                <!-- <div class="text-gray-900 dark:text-gray-100"><span class="font-medium text-gray-700 dark:text-gray-200">Negocio/Alias:</span> {{ $cliente->nombre_negocio }}</div> -->

                <div class="text-gray-900 dark:text-gray-100"><span
                        class="font-medium text-gray-700 dark:text-gray-200">Ciudad:</span> {{ $cliente->ciudad }}</div>
                <div class="text-gray-900 dark:text-gray-100">
                    <span class="font-medium text-gray-700 dark:text-gray-200">Status:</span>
                    <span
                        class="{{ $cliente->activo ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}">
                        {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>
@endif

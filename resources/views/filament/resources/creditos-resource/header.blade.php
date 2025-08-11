@php
$clienteIds = array_keys($clientes->toArray());
$currentIndex = array_search($clienteId, $clienteIds);
$anteriorId = $currentIndex > 0 ? $clienteIds[$currentIndex - 1] : null;
$siguienteId = isset($clienteIds[$currentIndex + 1]) ? $clienteIds[$currentIndex + 1] : null;
@endphp
{{-- SELECTOR DE CLIENTE CON NAVEGACIÓN Y FILTRO --}}
<div class="mb-6">
    <!-- Fila superior con selector de cliente -->
    <div class="flex items-center justify-center gap-4 mb-4">
        {{-- Botón anterior --}}
        <button wire:click="$set('clienteId', {{ $anteriorId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($anteriorId===null)>
            ◀
        </button>

        {{-- Select en el centro más pequeño --}}
        <div class="w-1/2">
            <label for="clienteId" class="block text-base font-semibold text-gray-700 mb-1 text-center">
                Seleccionar Cliente
            </label>
            <select wire:model="clienteId" id="clienteId"
                class="w-full border border-gray-300 rounded-md shadow-sm px-4 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">-- Seleccionar --</option>
                @foreach ($clientes as $id => $nombre)
                <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- Botón siguiente --}}
        <button wire:click="$set('clienteId', {{ $siguienteId ?? 'null' }})"
            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded-md disabled:opacity-50"
            @disabled($siguienteId===null)>
            ▶
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
                    deactivatingCreditId: null,
                    isRenewal: false,
                    mostrarRenovacionCompleta: false,

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
                        
                        if (descuentoAplicado < 0) {s
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
                        this.$watch('fechaActualEditable', () => this.calcularFechaVencimiento());

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
                        fechaBase.setDate(fechaBase.getDate() + diasPago);
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
                        console.log('data', creditData)
                        this.deactivatingCreditId = creditId;

                        this.clientName = creditData.clientName;
                        this.capital = creditData.capital;

                        this.interes = (creditData.capital * (creditData.interes / 100)).toFixed(2);
                        this.saldo = (creditData.capital + parseFloat(this.interes)).toFixed(2);
                        this.fechaInicio = this.formatearFecha(creditData.fechaInicio);
                        this.fechaVencimiento = this.formatearFecha(creditData.fechaVencimiento);
                        
                        // Inicializar la fecha actual editable con la fecha actual del sistema
                        this.fechaActualEditable = new Date().toISOString().split('T')[0];

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
                        
                        // Inicializar el saldo actual con el saldo real de la base de datos
                        this.saldoActual = saldoReal;

                        this.newValorCredito = creditData.capital;
                        this.newInteres = creditData.interes;
                        this.newFormaPago = '';
                        this.newCuenta = '';
                        this.newValorCuota = '';
                        this.newVencimientoDate = creditData.fechaVencimiento || '';
                        
                        // Resetear el descuento
                        this.descuento = 0;

                        this.isRenewal = isRenewal;
                        this.showDeactivationModal = true;
                        this.open = false;
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

                        // Calcular el interés adicional sobre el CAPITAL ORIGINAL
                        const mesesExactos = diasPago / 30;
                        const interesMensual = capitalOriginal * (interes / 100);
                        const interesAdicional = interesMensual * mesesExactos;

                        // El descuento reduce el monto base sobre el cual se calcula el nuevo crédito
                        const saldoConDescuento = saldoActual - descuento;
                        const totalPagar = saldoConDescuento + interesAdicional;
                        const cuotaDiaria = totalPagar / diasPago;
                        this.newValorCuota = cuotaDiaria.toFixed(2);
                        this.newCuenta = totalPagar.toFixed(2);

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

                    confirmDeactivation() {
                        if (this.isRenewal) {

                            // Armar payload
                            const payload = {
                                id: this.deactivatingCreditId,
                                valor_credito: parseFloat(this.aEntregar),
                                porcentaje_interes: parseFloat(this.newInteres),
                                dias_plazo: parseInt(this.newFormaPago),
                                fecha_vencimiento: this.newVencimientoDate,
                                fecha_credito: this.fechaActualEditable, // Agregar fecha editada
                                valor_cuota: parseFloat(this.newValorCuota),
                                numero_cuotas: parseInt(this.newNumeroCuotas) || 1,
                                descuento: parseFloat(this.descuento) || 0, // Agregar descuento
                                medios_pago: this.pagos
                                    .filter(mp => mp.tipo && mp.monto && !isNaN(parseFloat(mp.monto)))
                                    .map(mp => ({
                                        tipo: mp.tipo,
                                        monto: parseFloat(mp.monto)
                                    }))
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
                                    this.showDeactivationModal = false;
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
                                    fecha_credito: this.fechaActualEditable, // Agregar fecha editada
                                    descuento: parseFloat(this.descuento) || 0, // Agregar descuento
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.message) {
                                    alert(data.message);
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

                    confirmCancellation() {
                        @php
                            $creditoActivo = $cliente->creditoActivo();
                        @endphp

                        @if($creditoActivo)
                        // Verificar que el saldo actual sea mayor a 0
                        const saldoActual = {{ $creditoActivo->saldo_actual ?? 0 }};

                        if (saldoActual <= 0) {
                            alert('No se puede cancelar un crédito con saldo actual de 0 o menor.');
                            return;
                        }

                        // Confirmar la cancelación
                        if (confirm(`¿Está seguro de que desea cancelar este crédito?\nSaldo actual: S/ ${saldoActual.toFixed(2)}`)) {
                            const creditoId = {{ $creditoActivo->id_credito ?? 'null' }};

                            if (!creditoId) {
                                alert('Error: No se pudo obtener el ID del crédito.');
                                return;
                            }

                            fetch('/creditos/cancelar', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    credito_id: creditoId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message || 'Crédito cancelado exitosamente.');
                                    location.reload();
                                } else {
                                    alert(data.error || 'Error al cancelar el crédito.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error de conexión al cancelar el crédito.');
                            });
                        }
                        @else
                        alert('No hay crédito activo para cancelar.');
                        @endif
                    }

                }" x-init="init()" class="flex items-center space-x-2">
            {{-- Botón Editar Cliente --}}
            <a href="{{ route('filament.resources.clientes.edit', ['record' => $cliente->id_cliente]) }}"
                class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Editar Cliente
            </a>

            @if($cliente->creditos->isNotEmpty())
            {{-- Grupo para el botón Editar Crédito y el Dropdown de Acciones --}}
            <div class="flex items-center space-x-2">
                @php
                $creditoActivo = $cliente->creditoActivo();
                @endphp

                @if($creditoActivo)
                <a href="{{ route('filament.resources.creditos.edit', ['record' => $creditoActivo->id_credito]) }}"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Editar Crédito
                </a>

                <div class="relative inline-block text-left z-20">
                    <div>
                        <button type="button" @click="open = !open"
                            class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                            <!-- <a href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-0">Nuevo Préstamo</a> -->
                            @if($cliente->creditos->isEmpty() || $cliente->creditos->every(fn($credito) =>
                            $credito->saldo_actual <= 0)) <a
                                href="{{ route('filament.resources.creditos.create', ['cliente_id' => $cliente->id_cliente]) }}"
                                class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem"
                                tabindex="-1" id="menu-item-0">
                                Nuevo Préstamo
                                </a>
                                @endif

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
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

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
                                                <span x-text="fechaVencimiento" class="text-lg"></span>
                                            </div>
                                            <div>
                                                <span class="block text-sm text-gray-600">Días Transcurridos</span>
                                                <span x-text="calcularDiasTranscurridos(fechaInicio)"
                                                    class="text-lg text-indigo-700"></span>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <!-- <p class="mt-1 text-xs text-gray-500">El descuento se restará del saldo actual para calcular la nueva cuenta</p> -->
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="nuevo-interes"
                                                    class="block text-sm font-medium text-gray-700">Nuevo
                                                    Interés</label>
                                                <input type="number" step="0.01" id="nuevo-interes" x-model="newInteres"
                                                    @input="calcularFormaPagoYVencimiento()"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                                                <input type="date" id="fecha-actual"
                                                    x-model="fechaActualEditable"
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
                                </div>
                            </div>
                            <template x-if="isRenewal && !mostrarRenovacionCompleta">
                                <div class="mt-6 text-sm text-gray-600 italic">
                                    Completa el <strong>nuevo interés</strong> y la <strong>forma de pago</strong> para
                                    continuar...
                                </div>
                            </template>
                            <template x-if="isRenewal && mostrarRenovacionCompleta">
                                <div class="mt-4 space-y-3">
                                    <h1 class="text-md font-semibold text-gray-800 mb-2">Renovación</h1>
                                    <!-- Saldo Actual -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Saldo Actual</label>
                                        <input type="number" x-model="saldoActual"
                                            class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" readonly
                                            disabled />
                                    </div>
                                    <!-- Renovación -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Renovación</label>
                                        <input type="number" x-model="renovacion"
                                            class="mt-1 block w-full border-gray-300 rounded-md" />
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Nueva Fecha -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nueva Fecha</label>
                                            <input type="date" x-model="newFecha"
                                                class="mt-1 block w-full border-gray-300 rounded-md bg-gray-100"
                                                readonly />
                                        </div>

                                        <!-- Nueva Fecha de Vencimiento -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nueva Fecha de
                                                Vencimiento</label>
                                            <input type="date" x-model="newFechaVencimiento"
                                                class="mt-1 block w-full border-gray-300 rounded-md bg-gray-100"
                                                readonly />
                                        </div>
                                    </div>
                                    <!-- A entregar -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">A entregar</label>
                                        <input type="number" :value="aEntregar.toFixed(2)" readonly
                                            class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                    </div>
                                    <!-- Valor del nuevo crédito -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Valor del Crédito</label>
                                        <input type="number" :value="valorCredito.toFixed(2)" readonly
                                            class="mt-1 block w-full border-gray-300 rounded-md" />
                                    </div>
                                    <!-- Selección de medios de pago -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Agregar medio de
                                            pago</label>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <select x-model="medioSeleccionado" class="border-gray-300 rounded-md">
                                                <option value="" disabled selected>Seleccione...</option>
                                                <template x-for="medio in mediosDisponibles" :key="medio">
                                                    <option x-text="medio" :value="medio"></option>
                                                </template>
                                            </select>
                                            <input type="number" x-model="montoTemporal" min="0"
                                                class="border-gray-300 rounded-md w-36" placeholder="Monto" />
                                            <button type="button" @click="agregarMedioPago"
                                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                Agregar
                                            </button>
                                        </div>

                                        <!-- Lista de pagos agregados -->
                                        <div class="mt-4 space-y-2">
                                            <template x-for="(pago, index) in pagos" :key="pago.tipo">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-28 text-gray-700 font-medium" x-text="pago.tipo"></span>
                                                    <input type="number" x-model="pago.monto"
                                                        class="flex-1 border-gray-300 rounded-md" placeholder="Monto" />
                                                    <button type="button" @click="eliminarMedioPago(index)"
                                                        class="text-red-600 hover:text-red-800 font-bold text-sm">Eliminar</button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Total entregado -->
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700">Total entregado</label>
                                            <input type="number" :value="totalEntregado" readonly
                                                class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                        </div>
                                    </div>

                                    <!-- Lista de pagos agregados -->
                                    <div class="mt-4 space-y-2">
                                        <template x-for="(pago, index) in pagos" :key="pago.tipo">
                                            <div class="flex items-center space-x-2">
                                                <span class="w-28 text-gray-700 font-medium" x-text="pago.tipo"></span>
                                                <input type="number" x-model="pago.monto"
                                                    class="flex-1 border-gray-300 rounded-md" placeholder="Monto" />
                                                <button type="button" @click="eliminarMedioPago(index)"
                                                    class="text-red-600 hover:text-red-800 font-bold text-sm">Eliminar</button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Total entregado -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Total entregado</label>
                                        <input type="number" :value="totalEntregado" readonly
                                            class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md" />
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">

                            <button type="button" @click="confirmDeactivation()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                x-text="isRenewal ? 'Confirmar Renovación' : 'Confirmar Baja de Cuenta'">
                            </button>

                            <!--
                            <button type="button" @click="console.log({
                                        saldoActual,
                                        renovacion,
                                        newFecha,
                                        newFechaVencimiento,
                                        aEntregar,
                                        valorCredito,
                                        pagos,
                                        totalEntregado
                                    })" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Ver datos en consola
                            </button>
                        -->

                            <button type="button" @click="showDeactivationModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Información desplegable --}}
    <div x-data="{ open: false }" class="px-6 py-4">
        <button @click="open = !open" type="button"
            class="text-primary-600 hover:text-primary-900 text-sm font-medium flex items-center focus:outline-none">
            <span x-text="open ? '▲ Ocultar información' : '▼ Ver información del cliente'"></span>
        </button>

        <div x-show="open" x-transition class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="font-medium">Documento:</span> {{ $cliente->numero_documento }}</div>
            <div><span class="font-medium">Celular:</span> {{ $cliente->celular }}</div>
            <div><span class="font-medium">Teléfono:</span> {{ $cliente->telefono }}</div>
            <div><span class="font-medium">Dirección:</span> {{ $cliente->direccion }}</div>
            <!-- <div><span class="font-medium">Negocio/Alias:</span> {{ $cliente->nombre_negocio }}</div> -->

            <div><span class="font-medium">Ciudad:</span> {{ $cliente->ciudad }}</div>
            <div>
                <span class="font-medium">Status:</span>
                <span class="{{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">
                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

</div>
@endif

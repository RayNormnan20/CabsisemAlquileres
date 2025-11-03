<x-filament::page>
    <style>
        /* Contenedor de tabla para escritorio */
        .desktop-abonos-table { display: none; }
        /* En móvil, mostramos tarjetas; en escritorio mostramos la tabla */
        @media (min-width: 768px) {
            .responsive-abonos { display: none; }
            .desktop-abonos-table { display: block; }
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
        .card-header { padding: .75rem 1rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-weight: 600; font-size: 1rem; }
        .badge { display: inline-block; padding: .125rem .5rem; border-radius: .5rem; font-size: .75rem; }
        .badge-abono { background: #eff6ff; color: #1d4ed8; }
        .badge-devolucion { background: #fff7ed; color: #c2410c; }
        .card-body { padding: .75rem 1rem; }
        .row { display: flex; flex-wrap: wrap; gap: .75rem; }
        .col { flex: 1 1 45%; min-width: 45%; }
        .label { font-size: .75rem; color: #6b7280; }
        .value { font-weight: 600; color: #111827; }
        .actions { padding: .75rem 1rem; border-top: 1px solid #e5e7eb; display: flex; gap: .5rem; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: .25rem; padding: .5rem .75rem; font-size: .875rem; border-radius: .5rem; border: 1px solid #e5e7eb; background: #f9fafb; color: #111827; text-decoration: none; }
        .btn-primary { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
        .btn-danger { background: #dc2626; color: #fff; border-color: #dc2626; }
        .btn-info { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
        .conceptos { margin-top: .5rem; }
        .concepto-item { display: flex; justify-content: space-between; font-size: .875rem; padding: .25rem 0; border-bottom: 1px dashed #e5e7eb; }
        .concepto-item:last-child { border-bottom: none; }
        .empty { text-align: center; color: #6b7280; padding: 1rem; }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-abono {
            background-color: #10b981;
            color: white;
        }

        .badge-devolucion {
            background-color: #ef4444;
            color: white;
        }
    </style>

    <div class="responsive-abonos">
        @php
            $user = auth()->user();
        @endphp

        @if(isset($records) && count($records) > 0)
            @foreach($records as $record)
                @php
                    $clienteNombre = optional($record->cliente)->nombre_completo ?? 'Cliente';
                    $esDevolucion = (bool) $record->es_devolucion;
                    $credito = $record->credito ?? null;
                    $conceptos = $record->conceptosabonos ?? collect();
                @endphp
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{{ $clienteNombre }}</div>
                        <div>
                            <span class="badge {{ $esDevolucion ? 'badge-devolucion' : 'badge-abono' }}">{{ $esDevolucion ? 'Devolución' : 'Abono' }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="label">Fecha</div>
                                <div class="value">{{ 
                                    optional($record->fecha_pago)->format('d/m/Y H:i') ?? ($record->fecha_pago ? 
                                        \Carbon\Carbon::parse($record->fecha_pago)->format('d/m/Y H:i') : '-' ) 
                                }}</div>
                            </div>
                            <div class="col">
                                <div class="label">Usuario</div>
                                <div class="value">{{ optional($record->usuario)->name ?? '-' }}</div>
                            </div>
                        </div>
                        {{--
                        <div class="row">
                            <div class="col">
                                <div class="label">Cliente</div>
                                <div class="value">{{ $clienteNombre }}</div>
                            </div>
                            <div class="col">
                                <div class="label">Concepto</div>
                                <div class="value">{{ $esDevolucion ? 'Devolución' : 'Abono' }}</div>
                            </div>
                        </div>
                        --}}
                        <div class="row">
                            <div class="col">
                                <div class="label">Forma de Pago</div>
                                <div class="value">{{ optional($record->credito?->tipoPago)->nombre ?? 'Diario' }}</div>
                            </div>
                            <div class="col">
                                <div class="label">Cantidad</div>
                                <div class="value">S/ {{ number_format($record->monto_abono ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="row">
                            {{--
                            <div class="col">
                                <div class="label">Tipo</div>
                                <div class="value">
                                    <span class="badge {{ $esDevolucion ? 'badge-devolucion' : 'badge-abono' }}">
                                        {{ $esDevolucion ? 'Devolución' : 'Abono' }}
                                    </span>
                                </div>
                            </div>
                            --}}
                            <div class="col">
                                <div class="label">Detalle</div>
                                <div class="value">
                                    @php
                                        $detalleTipos = $conceptos && $conceptos->count()
                                            ? $conceptos->map(fn($c) => $c->tipo_concepto)->implode(' | ')
                                            : '-';
                                    @endphp
                                    {{ $detalleTipos }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="label">Monto</div>
                                <div class="value">
                                    @php
                                        $detalleMontos = $conceptos && $conceptos->count()
                                            ? $conceptos->map(fn($c) => 'S/ ' . number_format($c->monto ?? 0, 2))->implode(' | ')
                                            : 'S/ 0.00';
                                    @endphp
                                    {{ $detalleMontos }}
                                </div>
                            </div>
                        </div>

                        <div class="conceptos">
                            <div class="label">Detalle</div>
                            @if($conceptos && $conceptos->count())
                                @foreach($conceptos as $c)
                                    <div class="concepto-item">
                                        <span>{{ $c->tipo_concepto }}</span>
                                        <span>S/ {{ number_format($c->monto ?? 0, 2) }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="empty">Sin conceptos</div>
                            @endif
                        </div>
                    </div>
                    <div class="actions">
                        @if($credito)
                            <a class="btn" href="{{ route('filament.resources.creditos.historial-cliente', ['cliente' => $record->id_cliente]) }}">Ver crédito</a>
                        @endif

                        @can('view', $record)
                            @php
                                $comprobante = null;
                                if($conceptos && $conceptos->count()) {
                                    $comprobante = $conceptos->first(function($c){ return !empty($c->foto_comprobante); });
                                }
                            @endphp
                            @if($comprobante)
                                <button class="btn btn-info" type="button" wire:click="openComprobanteModal({{ $record->getKey() }})">Ver comprobante</button>
                            @endif
                        @endcan

                        @can('update', $record)
                            <a class="btn btn-primary" href="{{ route('filament.resources.abonos.edit', ['record' => $record]) }}">Editar</a>
                        @endcan

                        @can('delete', $record)
                            <button class="btn btn-danger" onclick="eliminarAbonoConfirm({{ $record->id_abono }})">Eliminar</button>
                        @endcan
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty">No hay abonos para mostrar.</div>
        @endif
    </div>

    <!-- Diseño para PC - Tabla original de Filament -->
    <div class="desktop-abonos-table">
        {{ $this->table }}
    </div>

    {{-- Modal responsive para comprobantes (replica del visor del action "view") --}}
    @if($this->viewerOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" wire:ignore>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-4">
            <div x-data="{
                items: @js($this->viewerItems),
                index: {{ $this->viewerIndex }},
                prev() { if (this.index > 0) this.index--; },
                next() { if (this.index < this.items.length - 1) this.index++; }
            }" class="space-y-4">
                <div class="flex justify-between items-center mb-2">
                    <button type="button" @click="prev" :disabled="index === 0" class="px-3 py-2 bg-blue-600 text-white text-sm rounded disabled:bg-gray-300">Anterior</button>
                    <span class="text-sm font-semibold">Comprobante <span x-text="index+1"></span> de <span x-text="items.length"></span></span>
                    <button type="button" @click="next" :disabled="index === items.length - 1" class="px-3 py-2 bg-blue-600 text-white text-sm rounded disabled:bg-gray-300">Siguiente</button>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs p-2 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium text-gray-500">Usuario</p>
                        <p x-text="items[index].usuario"></p>
                        <p class="font-medium text-gray-500 mt-2">Cliente</p>
                        <p x-text="items[index].cliente"></p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-500">Fecha</p>
                        <p x-text="items[index].fecha"></p>
                        <p class="font-medium text-gray-500 mt-2">Nombre Yape</p>
                        <p x-text="items[index].yape_nombre" class="text-red-600 font-bold"></p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-500">Métodos de pago</p>
                        <p x-text="items[index].metodos"></p>
                        <p class="font-medium text-gray-500">Monto</p>
                        <p>S/ <span x-text="items[index].monto"></span></p>
                    </div>
                </div>
                <template x-if="items[index].url">
                    <div class="flex justify-center">
                        <img :src="items[index].url" class="rounded max-h-[290px] object-contain cursor-pointer" @click="window.open(items[index].url, '_blank')">
                    </div>
                </template>
                <template x-if="!items[index].url">
                    <p class="text-center text-gray-400">No hay comprobante disponible</p>
                </template>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button class="px-3 py-2 bg-gray-200 rounded" wire:click="closeComprobanteModal">Cerrar</button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function eliminarAbonoConfirm(abonoId) {
            if (!abonoId) return;
            if (confirm('¿Seguro que deseas eliminar este abono? Esta acción no se puede deshacer.')) {
                const lw = window.Livewire || window.livewire;
                if (lw && typeof lw.emit === 'function') {
                    lw.emit('eliminarAbono', abonoId);
                } else {
                    alert('No se pudo comunicar con Livewire. Recarga la página e intenta nuevamente.');
                }
            }
        }
    </script>
</x-filament::page>
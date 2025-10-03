{{-- Vista responsiva para listado de créditos --}}
<x-filament::page>
    <div class="creditos-responsive-container">
        <style>
        .creditos-responsive-container {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Diseño para móviles - Cards como historial de abonos */
        .mobile-creditos-container {
            display: block;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
        }

        .credito-item {
            background: white;
            margin-bottom: 0.75rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border-left: 4px solid #3b82f6;
        }

        .credito-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .credito-cliente {
            font-weight: 600;
            font-size: 0.9rem;
            color: #2c3e50;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .credito-estado {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }

        .estado-activo {
            background-color: #d4edda;
            color: #155724;
        }

        .estado-pagado {
            background-color: #f8d7da;
            color: #721c24;
        }

        .credito-body {
            padding: 0.75rem 1rem;
        }

        .credito-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .credito-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .credito-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .credito-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .credito-detalle {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: #6c757d;
        }

        .credito-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e9ecef;
        }

        .credito-action-btn {
            flex: 1;
            padding: 0.5rem;
            border-radius: 6px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .empty-state-creditos {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }

        /* Tabla para PC - oculta en móvil */
        .desktop-creditos-table {
            display: none;
        }

        /* Media queries para responsividad */
        @media (min-width: 768px) {
            .mobile-creditos-container {
                display: none;
            }

            .desktop-creditos-table {
                display: block;
            }
        }

        /* Estilos para fechas vencidas */
        .fecha-vencida {
            color: #dc3545;
            font-weight: bold;
        }

        .fecha-normal {
            color: #28a745;
        }
        </style>

        <!-- Diseño para móviles - Cards -->
        <div class="mobile-creditos-container">
            @if($records && $records->count() > 0)
            @foreach($records as $record)
            <div class="credito-item">
                <div class="credito-header">
                    @if(!property_exists($this, 'clienteId') || !$this->clienteId)
                    <div class="credito-cliente">{{ $record->cliente->nombre_completo ?? 'Sin cliente' }}</div>
                    @else
                    <div class="credito-cliente">Crédito #{{ $record->id_credito }}</div>
                    @endif
                    <div class="credito-estado {{ $record->saldo_actual > 0 ? 'estado-activo' : 'estado-pagado' }}">
                        {{ $record->saldo_actual > 0 ? 'ACTIVO' : 'PAGADO' }}
                    </div>
                </div>
                <div class="credito-body">
                    <div class="credito-info">
                        <span class="credito-label">Fecha:</span>
                        <span class="credito-value">{{ $record->fecha_credito->format('d/m/Y') }}</span>
                    </div>
                    <div class="credito-info-grid">
                        <div class="credito-info">
                            <span class="credito-label">Valor:</span>
                            <span class="credito-value">S/ {{ number_format($record->valor_credito, 2) }}</span>
                        </div>
                        <div class="credito-info">
                            <span class="credito-label">Saldo:</span>
                            <span class="credito-value">S/ {{ number_format($record->saldo_actual, 2) }}</span>
                        </div>
                    </div>
                    <div class="credito-info-grid">
                        <div class="credito-info">
                            <span class="credito-label">Cuota:</span>
                            <span class="credito-value">
                                @if($record->es_adicional)
                                S/ {{ number_format($record->porcentaje_interes, 2) }}
                                @else
                                S/ {{ number_format($record->valor_cuota, 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="credito-info">
                            <span class="credito-label">Interés:</span>
                            <span class="credito-value"> {{ number_format($record->porcentaje_interes) }} %</span>
                        </div>
                    </div>
                    <div class="credito-info">
                        <span class="credito-label">Vencimiento:</span>
                        <span
                            class="credito-value {{ now()->gt($record->fecha_vencimiento) ? 'fecha-vencida' : 'fecha-normal' }}">
                            {{ $record->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </div>
                    @if($record->tipoPago)
                    <div class="credito-info">
                        <span class="credito-label">Tipo:</span>
                        <span class="credito-value">{{ $record->tipoPago->nombre }}</span>
                    </div>
                    @endif
                    @if($record->conceptosCredito && $record->conceptosCredito->count() > 0)
                    <div class="credito-detalle">
                        <strong>Detalle:</strong>
                        {{ $record->conceptosCredito->map(fn($c) => "{$c->tipo_concepto}: S/ " . number_format($c->monto, 2))->join(' | ') }}
                    </div>
                    @endif
                    <div class="credito-actions">
                        @php
                        // Verificar si el crédito tiene abonos
                        $tieneAbonos = $record->abonos()->exists();
                        @endphp

                        {{-- Siempre mostrar Ver Historial (accede por crédito específico) --}}
                        <a href="{{ route('filament.resources.creditos.historial-credito', ['credito' => $record->id_credito]) }}"
                            class="credito-action-btn btn-primary">
                            Ver Historial
                        </a>

                        @if(!$tieneAbonos)
                        {{-- Si no tiene abonos, también mostrar botón para eliminar --}}
                        <button onclick="eliminarCredito({{ $record->id_credito }})"
                            class="credito-action-btn btn-danger">
                            Eliminar
                        </button>
                        @endif
                        @if($record->conceptosCredito->where('foto_comprobante', '!=', null)->isNotEmpty())
                        <button class="credito-action-btn btn-secondary"
                            onclick="Livewire.emit('openModal', 'modal-comprobantes', {{ json_encode(['creditoId' => $record->id_credito]) }})">
                            Ver Comprobantes
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="empty-state-creditos">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Sin créditos disponibles</div>
                <div>No hay créditos que coincidan con los filtros aplicados.</div>
            </div>
            @endif
        </div>

        <!-- Diseño para PC - Tabla original de Filament -->
        <div class="desktop-creditos-table">
            {{-- Aquí se renderiza la tabla original de Filament --}}
            {{ $this->table }}
        </div>
    </div>

    <script>
    function eliminarCredito(creditoId) {
        if (confirm('¿Está seguro de que desea eliminar este crédito?\n\nEsta acción no se puede deshacer.')) {
            // Usar Livewire para eliminar el crédito
            Livewire.emit('eliminarCredito', creditoId);
        }
    }
    </script>
</x-filament::page>

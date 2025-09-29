<div>
        <div class="historial-abonos-mobile">
            <style>
                .historial-abonos-mobile {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background-color: #f8f9fa;
                    border-radius: 12px;
                    overflow: hidden;
                    width: 100%;
                    max-width: 100%;
                    min-height: 100vh;
                }

                .mobile-header {
                    background: linear-gradient(135deg, #4a90e2, #357abd);
                    color: white;
                    padding: 1rem;
                    text-align: center;
                    font-size: 1.1rem;
                    font-weight: 600;
                }

                .mobile-container {
                    padding: 1rem;
                    min-height: calc(100vh - 80px);
                    overflow-y: auto;
                }

                /* Responsive Design */
                @media (min-width: 768px) {
                    .historial-abonos-mobile {
                        min-height: 100vh;
                    }
                    
                    .mobile-container {
                        padding: 1.5rem;
                        min-height: calc(100vh - 120px);
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1rem;
                    }

                    .abono-item {
                        margin-bottom: 0;
                    }
                    
                    .mobile-header {
                        padding: 1.5rem;
                        font-size: 1.2rem;
                    }
                    
                    .total-section {
                        grid-column: 1 / -1;
                    }
                }

                @media (min-width: 1024px) {
                    .historial-abonos-mobile {
                        min-height: 100vh;
                    }
                    
                    .mobile-container {
                        padding: 2rem;
                        min-height: calc(100vh - 120px);
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1.5rem;
                    }
                    
                    .abono-item {
                        margin-bottom: 0;
                    }
                    
                    .total-section {
                        grid-column: 1 / -1;
                    }
                }

                .abono-item {
                    background: white;
                    margin-bottom: 0.75rem;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    overflow: hidden;
                }

                .abono-header {
                    background: #f8f9fa;
                    padding: 0.75rem 1rem;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .abono-tipo {
                    font-weight: 600;
                    font-size: 0.9rem;
                    color: #2c3e50;
                }

                .abono-monto {
                    font-weight: 700;
                    font-size: 1rem;
                    color: #27ae60;
                }

                .abono-monto.desembolso {
                    color: #e74c3c;
                }

                .abono-body {
                    padding: 0.75rem 1rem;
                }

                .abono-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 0.5rem;
                    font-size: 0.85rem;
                }

                .abono-label {
                    color: #7f8c8d;
                    font-weight: 500;
                }

                .abono-value {
                    color: #2c3e50;
                    font-weight: 600;
                }

                .abono-saldo {
                    background: #e8f4fd;
                    padding: 0.5rem;
                    border-radius: 8px;
                    text-align: center;
                    margin-top: 0.5rem;
                }

                .saldo-label {
                    font-size: 0.75rem;
                    color: #7f8c8d;
                    margin-bottom: 0.25rem;
                }

                .saldo-value {
                    font-size: 0.9rem;
                    font-weight: 700;
                    color: #2980b9;
                }

                .total-section {
                    background: white;
                    margin-top: 1rem;
                    padding: 1rem;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .total-label {
                    font-size: 1rem;
                    color: #7f8c8d;
                    font-weight: 600;
                }

                .total-value {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #27ae60;
                }

                .empty-state {
                    text-align: center;
                    padding: 2rem;
                    color: #7f8c8d;
                }

                .three-dots {
                    color: #bdc3c7;
                    font-size: 1.2rem;
                    float: right;
                    margin-top: -0.25rem;
                }

                /* Scrollbar personalizado */
                .mobile-container::-webkit-scrollbar {
                    width: 4px;
                }

                .mobile-container::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 2px;
                }

                .mobile-container::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 2px;
                }

                .mobile-container::-webkit-scrollbar-thumb:hover {
                    background: #a8a8a8;
                }
            </style>

            <div class="mobile-header">
                <div>Historial de Abonos</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">
                    Cliente: {{ $this->record->nombre }} {{ $this->record->apellido }}
                    @php
                        $creditoActivo = $this->getCreditoActivo();
                    @endphp
                </div>
            </div>

            <div class="mobile-container">
                @php
                    // Obtener los datos del historial
                    $widget = $this->getCachedTableQuery()->get();
                    $totalAbonos = 0;
                @endphp

                @if($widget->count() > 0)
                    @foreach($widget as $record)
                        @php
                            if($record->tipo_registro === 'abono' && !$record->es_devolucion) {
                                $totalAbonos += $record->monto_abono;
                            }
                        @endphp
                        
                        @if($record->tipo_registro === 'credito')
                            {{-- Diseño simple para Desembolsos (como imagen 1) --}}
                            <div class="abono-item">
                                <div class="abono-header">
                                    <div class="abono-tipo">{{ $record->concepto_nombre }}</div>
                                    <div class="abono-monto desembolso">S/{{ number_format($record->monto_abono, 2) }}</div>
                                </div>
                                <div class="abono-body" style="padding: 0.5rem 1rem;">
                                    <div style="font-size: 0.8rem; color: #7f8c8d;">
                                        {{ $record->fecha_pago->format('d M Y') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Diseño completo para Abonos (como imagen 2) --}}
                            <div class="abono-item">
                                <div class="abono-header">
                                    <div class="abono-tipo">
                                        {{ $record->concepto_nombre }}
                                        @if($record->es_devolucion)
                                            <span style="color: #e74c3c; font-size: 0.8rem;">(Devolución)</span>
                                        @endif
                                    </div>
                                    <div class="abono-monto">S/{{ number_format($record->monto_abono, 2) }}</div>
                                </div>
                                <div class="abono-body">
                                    <div class="abono-info">
                                        <span class="abono-label">Saldo</span>
                                        <span class="abono-value">S/{{ number_format($record->saldo_posterior, 2) }}</span>
                                    </div>
                                    @if($record->tipos_concepto)
                                    <div class="abono-info">
                                        <span class="abono-label">Tipo de Pago:</span>
                                        <span class="abono-value">{{ $record->tipos_concepto }}</span>
                                    </div>
                                    @endif
                                    <div class="abono-info">
                                        <span class="abono-label">{{ $record->fecha_pago->format('d M Y') }} • {{ $record->fecha_pago->format('H:i') }}</span>
                                        <span class="abono-value"></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="total-section">
                        <div class="total-label">Total Abonos:</div>
                        <div class="total-value">S/{{ number_format($totalAbonos, 2) }}</div>
                    </div>
                @else
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                        <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Sin historial de abonos</div>
                        <div>Este cliente no tiene abonos registrados.</div>
                    </div>
                @endif
            </div>
        </div>
</div>
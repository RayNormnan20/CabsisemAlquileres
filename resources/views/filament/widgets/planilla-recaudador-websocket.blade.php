<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo !== 'undefined') {
                console.log('Conectando a WebSocket para Planilla Recaudador...');
                
                // Suscribirse a los canales de planilla recaudador
                window.Echo.channel('planilla-recaudador')
                    .listen('.planilla-recaudador.created', (e) => {
                        console.log('Planilla Recaudador creada:', e);
                        
                        // Mostrar notificación usando el sistema nativo de Filament
                        if (window.filament && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Nueva entrada en Planilla Recaudador',
                                body: 'Se ha creado una nueva entrada: ' + (e.planillaRecaudador.cliente_completo || 'Cliente'),
                                status: 'success',
                                duration: 4000
                            });
                        } else {
                            // Fallback para mostrar notificación
                            console.log('✅ Nueva entrada en Planilla Recaudador:', e.planillaRecaudador.cliente_completo);
                        }
                        
                        // Actualizar componentes de forma inmediata y efectiva
                        console.log('📋 Nuevo crédito recibido:', e.planillaRecaudador);
                        
                        // Método 1: Actualización directa de Livewire
                        if (typeof Livewire !== 'undefined') {
                            // Actualizar todos los componentes Livewire de la página
                            Livewire.all().forEach(component => {
                                try {
                                    component.call('$refresh');
                                } catch (error) {
                                    console.log('Error actualizando componente:', error);
                                }
                            });
                            
                            // Emitir eventos específicos
                            Livewire.emit('refreshPlanillaRecaudador');
                            Livewire.emit('refreshPlanillaRecaudadorTable');
                        }
                        
                        // Método 2: Actualización de tablas Filament
                        setTimeout(() => {
                            // Buscar y actualizar tablas específicas
                            const tables = document.querySelectorAll('[wire\\:id*="table"], [wire\\:id*="list"], [wire\\:id*="planilla"]');
                            tables.forEach(table => {
                                const wireId = table.getAttribute('wire:id');
                                if (wireId) {
                                    const component = Livewire.find(wireId);
                                    if (component) {
                                        component.call('$refresh');
                                    }
                                }
                            });
                        }, 200);
                    })
                    .listen('.planilla-recaudador.updated', (e) => {
                        console.log('Planilla Recaudador actualizada:', e);
                        
                        // Mostrar notificación usando el sistema nativo de Filament
                        if (window.filament && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Planilla Recaudador actualizada',
                                body: 'Se ha actualizado: ' + (e.planillaRecaudador.cliente_completo || 'Cliente'),
                                status: 'info',
                                duration: 4000
                            });
                        } else {
                            // Fallback para mostrar notificación
                            console.log('ℹ️ Planilla Recaudador actualizada:', e.planillaRecaudador.cliente_completo);
                        }
                        
                        // Actualizar componentes Livewire
                        setTimeout(() => {
                            // Emitir eventos específicos para actualizar componentes
                            Livewire.emit('refreshPlanillaRecaudador');
                            Livewire.emit('refreshPlanillaRecaudadorTable');
                            Livewire.emit('$refresh');
                            
                            // Actualizar todas las tablas de Filament
                            document.querySelectorAll('[wire\\:id]').forEach(component => {
                                const wireId = component.getAttribute('wire:id');
                                if (wireId && (wireId.includes('planilla') || wireId.includes('recaudador') || wireId.includes('table'))) {
                                    Livewire.find(wireId)?.call('$refresh');
                                }
                            });
                        }, 100);
                    });
                

                
                console.log('WebSocket para Planilla Recaudador configurado correctamente');
            } else {
                console.warn('Echo no está disponible para WebSocket de Planilla Recaudador');
            }
        });
    </script>
</div>
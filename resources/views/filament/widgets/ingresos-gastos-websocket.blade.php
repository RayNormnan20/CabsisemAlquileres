<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Echo !== 'undefined') {
                console.log('🔌 Conectando WebSocket para Ingresos y Gastos...');
                
                // Suscribirse al canal de movimientos
                Echo.channel('movimientos')
                    .listen('.movimiento.created', (e) => {
                        console.log('📈 Nuevo movimiento creado:', e);
                        
                        // Mostrar notificación de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notify) {
                            window.filament.notify({
                                title: 'Nuevo Movimiento',
                                body: e.message || 'Se ha registrado un nuevo movimiento',
                                status: 'success',
                                duration: 4000
                            });
                        } else if (typeof $filament !== 'undefined' && $filament.notify) {
                            $filament.notify('success', e.message || 'Se ha registrado un nuevo movimiento');
                        }
                        
                        // Actualizar la tabla inmediatamente
                        setTimeout(() => {
                            console.log('🔄 Iniciando actualización de tablas...');
                            
                            if (typeof Livewire !== 'undefined') {
                                // Método 1: Actualizar solo componentes de tabla específicos
                                const tableComponents = document.querySelectorAll('[wire\\:id][class*="table"], [wire\\:id][class*="resource"], [wire\\:id][data-filament-table]');
                                tableComponents.forEach(element => {
                                    const wireId = element.getAttribute('wire:id');
                                    if (wireId) {
                                        try {
                                            const component = Livewire.find(wireId);
                                            if (component) {
                                                component.call('$refresh');
                                                console.log('📊 Tabla actualizada:', wireId);
                                            }
                                        } catch (error) {
                                            console.warn('Error actualizando tabla:', error);
                                        }
                                    }
                                });
                                
                                // Método 2: Emitir evento específico para tablas
                                Livewire.emit('refreshTable');
                                console.log('📡 Evento refreshTable emitido');
                                
                                // Método 3: Actualizar por wire:id
                                const wireElements = document.querySelectorAll('[wire\\:id]');
                                wireElements.forEach(element => {
                                    const wireId = element.getAttribute('wire:id');
                                    if (wireId) {
                                        try {
                                            const component = Livewire.find(wireId);
                                            if (component) {
                                                component.call('$refresh');
                                            }
                                        } catch (error) {
                                            console.warn('Error actualizando elemento wire:', error);
                                        }
                                    }
                                });
                                
                                // Método 4: Buscar botones de refresh de Filament
                                const refreshButtons = document.querySelectorAll('[wire\\:click*="refresh"], [wire\\:click="$refresh"]');
                                refreshButtons.forEach(button => {
                                    try {
                                        button.click();
                                        console.log('🔄 Botón de refresh clickeado');
                                    } catch (error) {
                                        console.warn('Error clickeando botón refresh:', error);
                                    }
                                });
                            }
                            
                            // Método 5: Actualizar tablas específicas de Filament
                            const filamentTables = document.querySelectorAll('[data-filament-table], .fi-ta-table');
                            filamentTables.forEach(table => {
                                const tableComponent = table.closest('[wire\\:id]');
                                if (tableComponent) {
                                    const wireId = tableComponent.getAttribute('wire:id');
                                    try {
                                        const component = Livewire.find(wireId);
                                        if (component) {
                                            component.call('$refresh');
                                            console.log('📊 Tabla Filament actualizada:', wireId);
                                        }
                                    } catch (error) {
                                        console.warn('Error actualizando tabla Filament:', error);
                                    }
                                }
                            });
                            
                            console.log('✅ Actualización automática completada sin reload');
                        }, 500);
                    })
                    .listen('.movimiento.updated', (e) => {
                        console.log('📊 Movimiento actualizado:', e);
                        
                        // Mostrar notificación de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notify) {
                            window.filament.notify({
                                title: 'Movimiento Actualizado',
                                body: e.message || 'Se ha actualizado un movimiento',
                                status: 'warning',
                                duration: 4000
                            });
                        } else if (typeof $filament !== 'undefined' && $filament.notify) {
                            $filament.notify('warning', e.message || 'Se ha actualizado un movimiento');
                        }
                        
                        // Actualizar la tabla inmediatamente
                        setTimeout(() => {
                            console.log('🔄 Iniciando actualización de tablas...');
                            
                            if (typeof Livewire !== 'undefined') {
                                // Método 1: Actualizar TODOS los componentes Livewire
                                Object.keys(Livewire.components.componentsById).forEach(componentId => {
                                    const component = Livewire.components.componentsById[componentId];
                                    if (component && component.call) {
                                        console.log('🔄 Actualizando componente:', component.name || componentId);
                                        try {
                                            component.call('$refresh');
                                        } catch (error) {
                                            console.warn('Error actualizando componente:', error);
                                        }
                                    }
                                });
                                
                                // Método 2: Emitir eventos globales
                                Livewire.emit('refreshComponent');
                                Livewire.emit('$refresh');
                                
                                // Método 3: Actualizar por wire:id
                                const wireElements = document.querySelectorAll('[wire\\:id]');
                                wireElements.forEach(element => {
                                    const wireId = element.getAttribute('wire:id');
                                    if (wireId) {
                                        try {
                                            const component = Livewire.find(wireId);
                                            if (component) {
                                                component.call('$refresh');
                                            }
                                        } catch (error) {
                                            console.warn('Error actualizando elemento wire:', error);
                                        }
                                    }
                                });
                                
                                // Método 4: Buscar botones de refresh de Filament
                                const refreshButtons = document.querySelectorAll('[wire\\:click*="refresh"], [wire\\:click="$refresh"]');
                                refreshButtons.forEach(button => {
                                    try {
                                        button.click();
                                        console.log('🔄 Botón de refresh clickeado');
                                    } catch (error) {
                                        console.warn('Error clickeando botón refresh:', error);
                                    }
                                });
                            }
                            
                            // Método 5: Fallback - recargar página después de 3 segundos si nada funciona
                            setTimeout(() => {
                                console.log('🔄 Fallback: Recargando página...');
                                window.location.reload();
                            }, 3000);
                        }, 500);
                    });
                    
                // También suscribirse al canal específico de ingresos-gastos
                Echo.channel('ingresos-gastos')
                    .listen('.movimiento.created', (e) => {
                        console.log('💰 Nuevo ingreso/gasto:', e);
                    })
                    .listen('.movimiento.updated', (e) => {
                        console.log('💱 Ingreso/gasto actualizado:', e);
                    });
            } else {
                console.warn('Echo no está disponible para WebSockets');
            }
        });
    </script>
</div>
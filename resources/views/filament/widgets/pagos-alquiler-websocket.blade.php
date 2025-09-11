<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Echo !== 'undefined') {
                Echo.channel('pagos-alquiler')
                    .listen('.pago-alquiler.created', (e) => {
                        console.log('Pago de alquiler creado:', e);
                        
                        // Mostrar notificación nativa de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Nuevo Pago de Alquiler',
                                body: 'Se ha registrado un nuevo pago de alquiler',
                                status: 'success',
                                duration: 4000
                            });
                        }
                        
                        // Actualizar la tabla con múltiples métodos
                        setTimeout(() => {
                            if (typeof Livewire !== 'undefined') {
                                // Método 1: Emit eventos
                                Livewire.emit('refreshComponent');
                                Livewire.emit('refreshPagosAlquilerTable');
                                Livewire.emit('$refresh');
                                
                                // Método 2: Buscar componente específico
                                const tableComponent = Livewire.find('pagos-alquiler-table');
                                if (tableComponent) {
                                    tableComponent.call('$refresh');
                                }
                                
                                // Método 3: Refrescar página como fallback
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                // Fallback inmediato: recargar la página
                                window.location.reload();
                            }
                        }, 100);
                    })
                    .listen('.pago-alquiler.updated', (e) => {
                        console.log('Pago de alquiler actualizado:', e);
                        
                        // Mostrar notificación nativa de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Pago de Alquiler Actualizado',
                                body: 'Se ha actualizado un pago de alquiler',
                                status: 'info',
                                duration: 4000
                            });
                        }
                        
                        // Actualizar la tabla con múltiples métodos
                        setTimeout(() => {
                            if (typeof Livewire !== 'undefined') {
                                // Método 1: Emit eventos
                                Livewire.emit('refreshComponent');
                                Livewire.emit('refreshPagosAlquilerTable');
                                Livewire.emit('$refresh');
                                
                                // Método 2: Buscar componente específico
                                const tableComponent = Livewire.find('pagos-alquiler-table');
                                if (tableComponent) {
                                    tableComponent.call('$refresh');
                                }
                                
                                // Método 3: Refrescar página como fallback
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                // Fallback inmediato: recargar la página
                                window.location.reload();
                            }
                        }, 100);
                    });
            } else {
                console.warn('Echo no está disponible para WebSockets');
            }
        });
    </script>
</div>
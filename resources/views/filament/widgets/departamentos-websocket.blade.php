<div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Echo !== 'undefined') {
            Echo.channel('departamentos')
                .listen('.departamento.created', (e) => {
                    console.log('Departamento creado:', e);
                    
                    // Mostrar notificación nativa de Filament
                    if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                        window.filament.notifications.send({
                            title: 'Nuevo Departamento',
                            body: 'Se ha creado un nuevo departamento',
                            status: 'success',
                            duration: 4000
                        });
                    }
                    
                    // Actualizar la tabla con múltiples métodos
                    setTimeout(() => {
                        if (typeof Livewire !== 'undefined') {
                            // Método 1: Emit eventos
                            Livewire.emit('refreshComponent');
                            Livewire.emit('refreshDepartamentosTable');
                            Livewire.emit('$refresh');
                            
                            // Método 2: Buscar componente específico
                            const tableComponent = Livewire.find('departamentos-table');
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
                .listen('.departamento.updated', (e) => {
                    console.log('Departamento actualizado:', e);
                    
                    // Mostrar notificación nativa de Filament
                    if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                        window.filament.notifications.send({
                            title: 'Departamento Actualizado',
                            body: 'Se ha actualizado un departamento',
                            status: 'info',
                            duration: 4000
                        });
                    }
                    
                    // Actualizar la tabla con múltiples métodos
                    setTimeout(() => {
                        if (typeof Livewire !== 'undefined') {
                            // Método 1: Emit eventos
                            Livewire.emit('refreshComponent');
                            Livewire.emit('refreshDepartamentosTable');
                            Livewire.emit('$refresh');
                            
                            // Método 2: Buscar componente específico
                            const tableComponent = Livewire.find('departamentos-table');
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
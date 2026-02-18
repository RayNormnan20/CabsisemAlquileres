<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo !== 'undefined') {
                // Suscribirse a los canales de conceptos de abonos
                window.Echo.channel('conceptos-abonos')
                    .listen('.concepto-abono.created', (e) => {
                        console.log('ConceptoAbono creado:', e);
                        
                        // Mostrar notificación usando el sistema nativo de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Nuevo Concepto de Abono',
                                body: e.message || 'Se ha creado un nuevo concepto de abono',
                                status: 'success',
                                duration: 4000
                            });
                        }
                        
                        // Actualizar la tabla con múltiples métodos
                        setTimeout(() => {
                            if (typeof window.Livewire !== 'undefined') {
                                // Método 1: Emit eventos
                                window.Livewire.emit('refreshComponent');
                                window.Livewire.emit('refreshConceptoAbonoTable');
                                window.Livewire.emit('$refresh');
                                
                                // Método 2: Buscar y actualizar todas las tablas Filament
                                const tables = document.querySelectorAll('[wire\\:id]');
                                tables.forEach(table => {
                                    const wireId = table.getAttribute('wire:id');
                                    if (wireId && window.Livewire.find(wireId)) {
                                        window.Livewire.find(wireId).call('$refresh');
                                    }
                                });
                            }
                        }, 100);
                    })
                    .listen('.concepto-abono.updated', (e) => {
                        console.log('ConceptoAbono actualizado:', e);
                        
                        // Mostrar notificación usando el sistema nativo de Filament
                        if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Concepto de Abono Actualizado',
                                body: e.message || 'Se ha actualizado un concepto de abono',
                                status: 'info',
                                duration: 4000
                            });
                        }
                        
                        // Actualizar la tabla con múltiples métodos
                        setTimeout(() => {
                            if (typeof window.Livewire !== 'undefined') {
                                // Método 1: Emit eventos
                                window.Livewire.emit('refreshComponent');
                                window.Livewire.emit('refreshConceptoAbonoTable');
                                window.Livewire.emit('$refresh');
                                
                                // Método 2: Buscar y actualizar todas las tablas Filament
                                const tables = document.querySelectorAll('[wire\\:id]');
                                tables.forEach(table => {
                                    const wireId = table.getAttribute('wire:id');
                                    if (wireId && window.Livewire.find(wireId)) {
                                        window.Livewire.find(wireId).call('$refresh');
                                    }
                                });
                            }
                        }, 100);
                    })
                    .listen('.concepto-abono.deleted', (e) => {
                        console.log('ConceptoAbono eliminado:', e);
                        
                        if (typeof window.filament !== 'undefined' && window.filament.notifications) {
                            window.filament.notifications.send({
                                title: 'Concepto de Abono Eliminado',
                                body: e.message || 'Se ha eliminado un concepto de abono',
                                status: 'warning',
                                duration: 4000
                            });
                        }
                        
                        setTimeout(() => {
                            if (typeof window.Livewire !== 'undefined') {
                                window.Livewire.emit('refreshComponent');
                                window.Livewire.emit('refreshConceptoAbonoTable');
                                window.Livewire.emit('$refresh');
                                
                                const tables = document.querySelectorAll('[wire\\:id]');
                                tables.forEach(table => {
                                    const wireId = table.getAttribute('wire:id');
                                    if (wireId && window.Livewire.find(wireId)) {
                                        window.Livewire.find(wireId).call('$refresh');
                                    }
                                });
                            }
                        }, 100);
                    });
            } else {
                console.warn('Echo no está disponible para ConceptoAbono WebSocket');
            }
        });
    </script>
</div>

<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo === 'undefined') {
                console.error('Echo no está disponible para WebSocket de clientes alquiler');
                return;
            }

            // Listener para cuando se crea un nuevo cliente alquiler
            window.Echo.channel('clientes-alquiler')
                .listen('.cliente-alquiler.created', (e) => {
                    console.log('Nuevo cliente alquiler creado:', e.clienteAlquiler);
                    
                    // Mostrar notificación usando el sistema nativo de Filament
                    if (typeof window.filament !== 'undefined' && window.filament.notify) {
                        window.filament.notify({
                            title: 'Nuevo cliente alquiler agregado',
                            body: `Se ha agregado el cliente: ${e.clienteAlquiler.nombre || 'Sin nombre'}`,
                            status: 'success'
                        });
                    }
                    
                    // Refrescar la tabla con múltiples métodos
                    setTimeout(() => {
                        if (typeof window.Livewire !== 'undefined') {
                            // Método 1: Emit eventos
                            window.Livewire.emit('refreshComponent');
                            window.Livewire.emit('refreshClienteAlquilerTable');
                            window.Livewire.emit('$refresh');
                            
                            // Método 2: Buscar y actualizar todas las tablas Filament
                            const tables = document.querySelectorAll('[wire\\:id]');
                            tables.forEach(table => {
                                const wireId = table.getAttribute('wire:id');
                                if (wireId && window.Livewire.find(wireId)) {
                                    window.Livewire.find(wireId).call('$refresh');
                                }
                            });
                        } else {
                            // Fallback si Livewire no está disponible
                            console.warn('Livewire no disponible, recargando página');
                            window.location.reload();
                        }
                    }, 100);
                });
            
            // Listener para cuando se actualiza un cliente alquiler
            window.Echo.channel('clientes-alquiler')
                .listen('.cliente-alquiler.updated', (e) => {
                    console.log('Cliente alquiler actualizado:', e.clienteAlquiler);
                    
                    // Mostrar notificación usando el sistema nativo de Filament
                    if (typeof window.filament !== 'undefined' && window.filament.notify) {
                        window.filament.notify({
                            title: 'Cliente alquiler actualizado',
                            body: `Se ha actualizado el cliente: ${e.clienteAlquiler.nombre || 'Sin nombre'}`,
                            status: 'info'
                        });
                    }
                    
                    // Refrescar la tabla con múltiples métodos
                    setTimeout(() => {
                        if (typeof window.Livewire !== 'undefined') {
                            // Método 1: Emit eventos
                            window.Livewire.emit('refreshComponent');
                            window.Livewire.emit('refreshClienteAlquilerTable');
                            window.Livewire.emit('$refresh');
                            
                            // Método 2: Buscar y actualizar todas las tablas Filament
                            const tables = document.querySelectorAll('[wire\\:id]');
                            tables.forEach(table => {
                                const wireId = table.getAttribute('wire:id');
                                if (wireId && window.Livewire.find(wireId)) {
                                    window.Livewire.find(wireId).call('$refresh');
                                }
                            });
                        } else {
                            // Fallback si Livewire no está disponible
                            console.warn('Livewire no disponible, recargando página');
                            window.location.reload();
                        }
                    }, 100);
                });
        });
    </script>
</div>
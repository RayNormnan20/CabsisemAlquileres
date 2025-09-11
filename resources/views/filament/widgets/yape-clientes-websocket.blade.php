<div>
    <script>
    // WebSocket listeners para actualizaciones en tiempo real de Yape Clientes
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Echo no está disponible para WebSocket de Yape Clientes');
            return;
        }

        // Suscribirse al canal público de yape-clientes
        const channel = window.Echo.channel('yape-clientes');
        
        // Escuchar eventos de yape clientes creados
        channel.listen('.yape-cliente.created', (data) => {
            console.log('Yape Cliente creado:', data);
            
            // Mostrar notificación usando el sistema nativo de Filament
            if (typeof window.filament !== 'undefined' && window.filament.notify) {
                window.filament.notify({
                    title: 'Nuevo Yape Cliente registrado',
                    body: `Se ha registrado: ${data.yapeCliente.nombre || 'Sin nombre'} - Monto: S/ ${data.yapeCliente.monto || '0.00'}`,
                    status: 'success'
                });
            } else {
                // Fallback para mostrar notificación
                console.log('Notificación:', data.message || 'Nuevo Yape Cliente registrado');
            }
            
            // Actualizar tabla con múltiples métodos y delay de sincronización
            setTimeout(() => {
                if (typeof window.Livewire !== 'undefined') {
                    // Método 1: Emit eventos
                    window.Livewire.emit('refreshComponent');
                    window.Livewire.emit('refreshYapeClientesTable');
                    window.Livewire.emit('$refresh');
                    console.log('Datos actualizados via Livewire');
                }
                
                // Método 2: Buscar y actualizar componentes específicos
                if (typeof window.filament !== 'undefined' && window.filament.tables) {
                    const tables = document.querySelectorAll('[wire\\:id]');
                    tables.forEach(table => {
                        const wireId = table.getAttribute('wire:id');
                        if (wireId && typeof window.Livewire.find === 'function') {
                            const component = window.Livewire.find(wireId);
                            if (component) {
                                component.call('$refresh');
                            }
                        }
                    });
                    console.log('Tablas Filament actualizadas');
                }
                
                // Método 3: Fallback - recargar página después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }, 100);
        });
        
        // Escuchar eventos de yape clientes actualizados
        channel.listen('.yape-cliente.updated', (data) => {
            console.log('Yape Cliente actualizado:', data);
            
            // Mostrar notificación usando el sistema nativo de Filament
            if (typeof window.filament !== 'undefined' && window.filament.notify) {
                window.filament.notify({
                    title: 'Yape Cliente actualizado',
                    body: `Se ha actualizado: ${data.yapeCliente.nombre || 'Sin nombre'} - Monto: S/ ${data.yapeCliente.monto || '0.00'}`,
                    status: 'info'
                });
            } else {
                // Fallback para mostrar notificación
                console.log('Notificación:', data.message || 'Yape Cliente actualizado');
            }
            
            // Actualizar tabla con múltiples métodos y delay de sincronización
            setTimeout(() => {
                if (typeof window.Livewire !== 'undefined') {
                    // Método 1: Emit eventos
                    window.Livewire.emit('refreshComponent');
                    window.Livewire.emit('refreshYapeClientesTable');
                    window.Livewire.emit('$refresh');
                    console.log('Datos actualizados via Livewire');
                }
                
                // Método 2: Buscar y actualizar componentes específicos
                if (typeof window.filament !== 'undefined' && window.filament.tables) {
                    const tables = document.querySelectorAll('[wire\\:id]');
                    tables.forEach(table => {
                        const wireId = table.getAttribute('wire:id');
                        if (wireId && typeof window.Livewire.find === 'function') {
                            const component = window.Livewire.find(wireId);
                            if (component) {
                                component.call('$refresh');
                            }
                        }
                    });
                    console.log('Tablas Filament actualizadas');
                }
                
                // Método 3: Fallback - recargar página después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }, 100);
        });
        
        console.log('WebSocket listeners para Yape Clientes inicializados');
    });
    </script>
</div>
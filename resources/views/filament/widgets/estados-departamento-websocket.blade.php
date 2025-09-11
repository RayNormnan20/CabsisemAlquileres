<div>
    <script>
    // WebSocket listeners para actualizaciones en tiempo real de Estados Departamento
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Echo !== 'undefined') {
            // Suscribirse al canal público de estados departamento
            const channel = window.Echo.channel('estados-departamento');
            
            // Escuchar eventos de estados departamento creados
            channel.listen('.estado-departamento.created', (data) => {
                console.log('Estado Departamento creado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('success', data.message || 'Nuevo estado departamento registrado');
                } else {
                    // Fallback para mostrar notificación
                    console.log('Notificación:', data.message || 'Nuevo estado departamento registrado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('refreshEstadoDepartamentoTable');
                        window.Livewire.emit('$refresh');
                        console.log('Datos actualizados via Livewire');
                    }
                    
                    // Actualizar tabla si existe
                    if (typeof window.filament !== 'undefined' && window.filament.tables) {
                        // Buscar y actualizar tablas de Filament
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
                    
                    // Como último recurso, recargar solo si no hay Livewire
                    if (typeof window.Livewire === 'undefined') {
                        window.location.reload();
                    }
                }, 500);
            });
            
            // Escuchar eventos de estados departamento actualizados
            channel.listen('.estado-departamento.updated', (data) => {
                console.log('Estado Departamento actualizado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('info', data.message || 'Estado departamento actualizado');
                } else {
                    console.log('Notificación:', data.message || 'Estado departamento actualizado');
                }
                
                // Actualizar datos usando Livewire
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('refreshEstadoDepartamentoTable');
                        window.Livewire.emit('$refresh');
                        console.log('Datos actualizados via Livewire');
                    }
                    
                    // Actualizar tabla si existe
                    if (typeof window.filament !== 'undefined' && window.filament.tables) {
                        // Buscar y actualizar tablas de Filament
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
                }, 500);
            });
            
            console.log('WebSocket listeners registrados para Estados Departamento');
        } else {
            console.log('Echo no está disponible, WebSocket listeners no registrados');
        }
    });
    </script>
</div>
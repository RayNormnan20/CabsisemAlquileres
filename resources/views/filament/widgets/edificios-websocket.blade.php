<div>
    <script>
    // WebSocket listeners para actualizaciones en tiempo real de Edificios
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Echo !== 'undefined') {
            // Suscribirse al canal público de edificios
            const channel = window.Echo.channel('edificios');
            
            // Escuchar eventos de edificios creados
            channel.listen('.edificio.created', (data) => {
                console.log('Edificio creado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('success', data.message || 'Nuevo edificio registrado');
                } else {
                    // Fallback para mostrar notificación
                    console.log('Notificación:', data.message || 'Nuevo edificio registrado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('refreshEdificiosTable');
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
                }, 100); // Pequeño delay para asegurar que Livewire esté listo
            });
            
            // Escuchar eventos de edificios actualizados
            channel.listen('.edificio.updated', (data) => {
                console.log('Edificio actualizado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('info', data.message || 'Edificio actualizado');
                } else {
                    console.log('Notificación:', data.message || 'Edificio actualizado');
                }
                
                // Actualizar datos
                setTimeout(() => {
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
                        window.Livewire.emit('refreshEdificiosTable');
                        window.Livewire.emit('$refresh');
                        console.log('Datos actualizados via Livewire');
                    }
                    
                    // Actualizar tabla si existe
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
                    
                    if (typeof window.Livewire === 'undefined') {
                        window.location.reload();
                    }
                }, 100);
            });
            
            console.log('WebSocket listeners para edificios configurados correctamente');
        } else {
            console.error('Echo no está disponible. Verifica la configuración de WebSocket.');
        }
    });
    </script>
</div>
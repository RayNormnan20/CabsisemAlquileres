{{-- Header con WebSocket listeners, los botones aparecerán automáticamente --}}
<div></div>

<script>
// WebSocket listeners para actualizaciones en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.Echo !== 'undefined') {
        // Obtener la ruta del usuario desde la sesión
        const rutaId = {!! json_encode(session('selected_ruta_id')) !!};
        const clienteId = {!! json_encode($clienteId ?? null) !!};
        
        if (rutaId) {
            // Suscribirse al canal público de la ruta
            const channel = window.Echo.channel(`ruta.${rutaId}`);
            
            // Escuchar eventos de clientes creados
            channel.listen('.cliente.created', (data) => {
                console.log('Cliente creado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('success', data.message || 'Nuevo cliente registrado');
                } else {
                    // Fallback para mostrar notificación
                    console.log('Notificación:', data.message || 'Nuevo cliente registrado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
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
            
            // Escuchar eventos de clientes actualizados
            channel.listen('.cliente.updated', (data) => {
                console.log('Cliente actualizado:', data);
                
                // Mostrar notificación
                if (typeof window.filament !== 'undefined' && window.filament.notify) {
                    window.filament.notify('info', data.message || 'Cliente actualizado');
                } else {
                    console.log('Notificación:', data.message || 'Cliente actualizado');
                }
                
                // Actualizar datos usando Livewire en lugar de recargar la página
                setTimeout(() => {
                    // Intentar actualizar usando Livewire
                    if (typeof window.Livewire !== 'undefined') {
                        window.Livewire.emit('refreshComponent');
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
            
            console.log('WebSocket listeners registrados para ruta:', rutaId);
        } else {
            console.log('No hay ruta seleccionada, no se pueden registrar listeners de WebSocket');
        }
    } else {
        console.error('Echo no está disponible. Verifica la configuración de WebSockets.');
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración WebSocket para {{ $channel }}
        const channel = '{{ $channel }}';
        const events = @json($events);
        const tableId = '{{ $tableId ?? "data-table" }}';
        
        console.log('Inicializando WebSocket para canal:', channel);
        console.log('Eventos a escuchar:', events);
        
        // Conectar a WebSocket
        const echo = window.Echo;
        if (!echo) {
            console.error('Laravel Echo no está disponible');
            return;
        }
        
        // Escuchar el canal
        const channelInstance = echo.channel(channel);
        
        // Función para refrescar la tabla
        function refreshTable() {
            console.log('Refrescando tabla...');
            
            // Buscar el botón de refresh de Filament
            const refreshButton = document.querySelector('[wire\\:click="\$refresh"]');
            if (refreshButton) {
                refreshButton.click();
                console.log('Tabla refrescada usando botón Filament');
                return;
            }
            
            // Alternativa: recargar la página si no se encuentra el botón
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
        
        // Registrar listeners para cada evento
        events.forEach(eventName => {
            channelInstance.listen('.' + eventName.split('.').pop(), (data) => {
                console.log(`Evento recibido: ${eventName}`, data);
                
                // Mostrar notificación
                if (window.filament) {
                    window.filament.notify({
                        title: 'Actualización automática',
                        body: 'Los datos han sido actualizados',
                        status: 'info',
                        duration: 3000
                    });
                }
                
                // Refrescar tabla después de un pequeño delay
                setTimeout(refreshTable, 1000);
            });
        });
        
        console.log('WebSocket configurado correctamente para', channel);
    });
</script>

<style>
    /* Estilos para indicador de actualización */
    .websocket-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .websocket-indicator.show {
        opacity: 1;
    }
</style>

<div id="websocket-indicator" class="websocket-indicator">
    🔄 Actualizando datos...
</div>
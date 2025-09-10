// WebSocket listeners para actualizaciones en tiempo real

// Función para inicializar los listeners de WebSocket
export function initializeWebSocketListeners() {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo no está disponible. Asegúrate de que esté configurado correctamente.');
        return;
    }

    // Escuchar canales públicos para todos los eventos
    
    // Canal para eventos de clientes
    window.Echo.channel('clientes')
        .listen('.cliente.created', (data) => {
            console.log('Nuevo cliente creado:', data);
            handleClienteCreated(data);
        })
        .listen('.cliente.updated', (data) => {
            console.log('Cliente actualizado:', data);
            handleClienteUpdated(data);
        });

    // Canal para eventos de créditos
    window.Echo.channel('creditos')
        .listen('.credito.created', (data) => {
            console.log('Nuevo crédito creado:', data);
            handleCreditoCreated(data);
        })
        .listen('.credito.updated', (data) => {
            console.log('Crédito actualizado:', data);
            handleCreditoUpdated(data);
        });

    // Canal para eventos de abonos
    window.Echo.channel('abonos')
        .listen('.abono.created', (data) => {
            console.log('Nuevo abono creado:', data);
            handleAbonoCreated(data);
        })
        .listen('.abono.updated', (data) => {
            console.log('Abono actualizado:', data);
            handleAbonoUpdated(data);
        });

    console.log('WebSocket listeners inicializados para canales públicos');
}

// Configurar listeners de WebSocket
function setupWebSocketListeners() {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo no está disponible');
        return;
    }

    console.log('Configurando listeners para eventos de clientes');

    // Listener para eventos de clientes en canal público
    window.Echo.channel('clientes')
        .listen('cliente.created', (e) => {
            console.log('Cliente creado:', e);
            addClienteToTable(e.cliente);
            refreshClientesTable();
        })
        .listen('cliente.updated', (e) => {
            console.log('Cliente actualizado:', e);
            updateClienteInTable(e.cliente);
            refreshClientesTable();
        });
}

// Handlers para diferentes tipos de eventos

function handleCreditoCreated(data) {
    // Mostrar notificación
    showNotification('success', data.message || 'Nuevo crédito creado');
    
    // Actualizar tablas si están visibles
    refreshCreditosTable();
    
    // Actualizar contadores/estadísticas
    updateDashboardStats();
    
    // Si estamos en la página de créditos, agregar la nueva fila
    addCreditoToTable(data.credito);
}

function handleCreditoUpdated(data) {
    showNotification('info', data.message || 'Crédito actualizado');
    
    // Actualizar la fila específica en la tabla
    updateCreditoInTable(data.credito);
    
    // Actualizar estadísticas
    updateDashboardStats();
}

function handleClienteCreated(data) {
    showNotification('success', data.message || 'Nuevo cliente registrado');
    
    // Actualizar tabla de clientes
    refreshClientesTable();
    
    // Agregar cliente a la tabla si está visible
    addClienteToTable(data.cliente);
}

function handleClienteUpdated(data) {
    showNotification('info', data.message || 'Cliente actualizado');
    
    // Actualizar la fila del cliente
    updateClienteInTable(data.cliente);
}

function handleAbonoCreated(data) {
    showNotification('success', data.message || 'Nuevo abono registrado');
    
    // Actualizar tablas relacionadas
    refreshAbonosTable();
    refreshCreditosTable(); // Los saldos pueden haber cambiado
    
    // Actualizar estadísticas
    updateDashboardStats();
    
    // Agregar abono a la tabla
    addAbonoToTable(data.abono);
}

function handleAbonoUpdated(data) {
    showNotification('info', data.message || 'Abono actualizado');
    
    // Actualizar la fila del abono
    updateAbonoInTable(data.abono);
    
    // Actualizar créditos relacionados
    refreshCreditosTable();
}

// Funciones de utilidad para actualizar la UI

function showNotification(type, message) {
    // Implementar notificaciones (puede usar Toastr, SweetAlert, etc.)
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'info',
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        // Fallback a alert nativo
        alert(message);
    }
}

function refreshCreditosTable() {
    // Si estamos usando Livewire
    if (typeof Livewire !== 'undefined') {
        Livewire.emit('refreshTable');
    }
    
    // Si estamos usando DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        const table = $('#creditos-table').DataTable();
        if (table) {
            table.ajax.reload(null, false);
        }
    }
    
    // Si estamos usando Filament
    if (window.filamentData) {
        // Trigger refresh en Filament
        window.dispatchEvent(new CustomEvent('refresh-table'));
    }
}

function refreshClientesTable() {
    // Para Filament/Livewire
    if (typeof Livewire !== 'undefined') {
        // Refrescar todos los componentes de tabla de clientes
        Livewire.emit('refreshComponent');
        
        // Buscar componentes específicos de clientes
        const clientesComponents = document.querySelectorAll('[wire\\:id*="clientes"], [wire\\:id*="cliente"]');
        clientesComponents.forEach(component => {
            const wireId = component.getAttribute('wire:id');
            if (wireId && window.Livewire.find(wireId)) {
                window.Livewire.find(wireId).call('$refresh');
            }
        });
    }
    
    // Para DataTables tradicionales
    if (typeof $.fn.DataTable !== 'undefined') {
        const table = $('#clientes-table').DataTable();
        if (table) {
            table.ajax.reload(null, false);
        }
    }
    
    // Disparar evento personalizado para otros listeners
    window.dispatchEvent(new CustomEvent('clientes-table-refresh'));
}

function refreshAbonosTable() {
    if (typeof Livewire !== 'undefined') {
        Livewire.emit('refreshAbonosTable');
    }
    
    if (typeof $.fn.DataTable !== 'undefined') {
        const table = $('#abonos-table').DataTable();
        if (table) {
            table.ajax.reload(null, false);
        }
    }
}

function updateDashboardStats() {
    // Actualizar estadísticas del dashboard
    if (typeof Livewire !== 'undefined') {
        Livewire.emit('updateStats');
    }
    
    // O hacer una llamada AJAX para obtener nuevas estadísticas
    fetch('/api/dashboard-stats')
        .then(response => response.json())
        .then(data => {
            // Actualizar elementos del DOM con las nuevas estadísticas
            updateStatsElements(data);
        })
        .catch(error => console.error('Error actualizando estadísticas:', error));
}

function updateStatsElements(stats) {
    // Actualizar elementos específicos del dashboard
    const elements = {
        'total-creditos': stats.totalCreditos,
        'total-abonos': stats.totalAbonos,
        'saldo-pendiente': stats.saldoPendiente,
        'clientes-activos': stats.clientesActivos
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
}

function addCreditoToTable(credito) {
    // Implementar lógica para agregar una nueva fila a la tabla de créditos
    // Esto dependerá del framework/librería que estés usando
}

function updateCreditoInTable(credito) {
    // Implementar lógica para actualizar una fila existente
}

function addClienteToTable(cliente) {
    // Para Filament, simplemente refrescar la tabla
    refreshClientesTable();
    
    // Para DataTables, agregar fila dinámicamente si es posible
    if (typeof $.fn.DataTable !== 'undefined') {
        const table = $('#clientes-table').DataTable();
        if (table && cliente) {
            try {
                table.row.add([
                    cliente.id_cliente || '',
                    cliente.nombre_completo || (cliente.nombre + ' ' + cliente.apellido),
                    cliente.numero_documento || '',
                    cliente.celular || '',
                    'Activo'
                ]).draw(false);
            } catch (e) {
                // Si falla, hacer refresh completo
                table.ajax.reload(null, false);
            }
        }
    }
}

function updateClienteInTable(cliente) {
    // Para Filament, refrescar la tabla
    refreshClientesTable();
    
    // Para DataTables, buscar y actualizar la fila específica
    if (typeof $.fn.DataTable !== 'undefined' && cliente) {
        const table = $('#clientes-table').DataTable();
        if (table) {
            try {
                // Buscar la fila por ID del cliente
                const rowIndex = table.column(0).data().indexOf(cliente.id_cliente.toString());
                if (rowIndex !== -1) {
                    table.row(rowIndex).data([
                        cliente.id_cliente || '',
                        cliente.nombre_completo || (cliente.nombre + ' ' + cliente.apellido),
                        cliente.numero_documento || '',
                        cliente.celular || '',
                        cliente.activo ? 'Activo' : 'Inactivo'
                    ]).draw(false);
                } else {
                    // Si no se encuentra, hacer refresh completo
                    table.ajax.reload(null, false);
                }
            } catch (e) {
                table.ajax.reload(null, false);
            }
        }
    }
}

function addAbonoToTable(abono) {
    // Implementar lógica para agregar una nueva fila a la tabla de abonos
}

function updateAbonoInTable(abono) {
    // Implementar lógica para actualizar una fila de abono existente
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para asegurar que Echo esté disponible
    setTimeout(() => {
        initializeWebSocketListeners();
    }, 1000);
});

// También exportar para uso manual
window.initializeWebSocketListeners = initializeWebSocketListeners;
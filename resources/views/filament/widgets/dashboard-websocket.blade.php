<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo !== 'undefined') {
                const rutaId = {!! json_encode(session('selected_ruta_id')) !!};
                const notify = function(status, title, body) {
                    if (typeof window.filament !== 'undefined' && window.filament.notify) {
                        window.filament.notify({ title: title, body: body, status: status, duration: 3000 });
                    }
                };
                const refresh = function() {
                    setTimeout(() => {
                        if (typeof window.Livewire !== 'undefined') {
                            window.Livewire.emit('refreshComponent');
                            window.Livewire.emit('$refresh');
                        }
                        const wireElements = document.querySelectorAll('[wire\\:id]');
                        wireElements.forEach(el => {
                            const id = el.getAttribute('wire:id');
                            if (id) {
                                try {
                                    const c = window.Livewire.find(id);
                                    if (c) {
                                        c.call('$refresh');
                                    }
                                } catch (e) {}
                            }
                        });
                        const filamentTables = document.querySelectorAll('[data-filament-table], .fi-ta-table');
                        filamentTables.forEach(table => {
                            const comp = table.closest('[wire\\:id]');
                            if (comp) {
                                const id = comp.getAttribute('wire:id');
                                try {
                                    const c = window.Livewire.find(id);
                                    if (c) {
                                        c.call('$refresh');
                                    }
                                } catch (e) {}
                            }
                        });
                    }, 400);
                };
                if (rutaId) {
                    const channel = window.Echo.channel(`ruta.${rutaId}`);
                    channel.listen('.abono.created', e => { notify('success', 'Abono', e.message || 'Nuevo abono'); refresh(); });
                    channel.listen('.abono.deleted', e => { notify('warning', 'Abono', e.message || 'Abono eliminado'); refresh(); });
                    channel.listen('.credito.created', e => { notify('success', 'Crédito', e.message || 'Nuevo crédito'); refresh(); });
                    channel.listen('.credito.updated', e => { notify('info', 'Crédito', e.message || 'Crédito actualizado'); refresh(); });
                    channel.listen('.credito.deleted', e => { notify('warning', 'Crédito', e.message || 'Crédito eliminado'); refresh(); });
                    channel.listen('.yape-cliente.created', e => { notify('success', 'Yape', e.message || 'Nuevo Yape'); refresh(); });
                    channel.listen('.yape-cliente.updated', e => { notify('info', 'Yape', e.message || 'Yape actualizado'); refresh(); });
                }
                window.Echo.channel('movimientos')
                    .listen('.movimiento.created', e => { notify('success', 'Movimiento', e.message || 'Nuevo movimiento'); refresh(); })
                    .listen('.movimiento.updated', e => { notify('info', 'Movimiento', e.message || 'Movimiento actualizado'); refresh(); });
                window.Echo.channel('clientes')
                    .listen('.cliente.created', e => { notify('success', 'Cliente', e.message || 'Nuevo cliente'); refresh(); })
                    .listen('.cliente.updated', e => { notify('info', 'Cliente', e.message || 'Cliente actualizado'); refresh(); });
                window.Echo.channel('ingresos-gastos')
                    .listen('.movimiento.created', e => { refresh(); })
                    .listen('.movimiento.updated', e => { refresh(); });
            }
        });
    </script>
</div>

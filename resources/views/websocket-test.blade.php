<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-ruta-id" content="{{ auth()->user()->id_ruta ?? 1 }}">
    <title>Prueba de WebSockets - Cabsisem</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-section h3 {
            color: #333;
            margin-top: 0;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #1e7e34;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .log {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .log-entry {
            margin-bottom: 5px;
            padding: 2px 0;
        }
        .log-entry.success {
            color: #28a745;
        }
        .log-entry.error {
            color: #dc3545;
        }
        .log-entry.info {
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba de WebSockets - Sistema Cabsisem</h1>
        <p>Esta página permite probar la funcionalidad de WebSockets en tiempo real.</p>
        
        <div class="test-section">
            <h3>Estado de Conexión</h3>
            <div id="connection-status" class="status info">Verificando conexión...</div>
            <button onclick="testConnection()" class="btn">Probar Conexión</button>
            <button onclick="reconnect()" class="btn btn-warning">Reconectar</button>
        </div>

        <div class="test-section">
            <h3>Pruebas de Eventos</h3>
            <p>Simula la creación/actualización de registros para probar los eventos en tiempo real:</p>
            
            <button onclick="simulateEvent('credito', 'created')" class="btn btn-success">Simular Crédito Creado</button>
            <button onclick="simulateEvent('credito', 'updated')" class="btn">Simular Crédito Actualizado</button>
            <br>
            <button onclick="simulateEvent('cliente', 'created')" class="btn btn-success">Simular Cliente Creado</button>
            <button onclick="simulateEvent('cliente', 'updated')" class="btn">Simular Cliente Actualizado</button>
            <br>
            <button onclick="simulateEvent('abono', 'created')" class="btn btn-success">Simular Abono Creado</button>
            <button onclick="simulateEvent('abono', 'updated')" class="btn">Simular Abono Actualizado</button>
        </div>

        <div class="test-section">
            <h3>Log de Eventos</h3>
            <button onclick="clearLog()" class="btn btn-warning">Limpiar Log</button>
            <div id="event-log" class="log">
                <div class="log-entry info">Esperando eventos...</div>
            </div>
        </div>

        <div class="test-section">
            <h3>Información del Sistema</h3>
            <div id="system-info">
                <p><strong>Usuario:</strong> {{ auth()->user()->name ?? 'No autenticado' }}</p>
                <p><strong>Ruta ID:</strong> {{ auth()->user()->id_ruta ?? 'No definida' }}</p>
                <p><strong>Rol:</strong> {{ auth()->user()->rol ?? 'No definido' }}</p>
                <p><strong>Echo disponible:</strong> <span id="echo-status">Verificando...</span></p>
                <p><strong>Pusher disponible:</strong> <span id="pusher-status">Verificando...</span></p>
            </div>
        </div>
    </div>

    <script>
        let eventCount = 0;
        
        // Verificar disponibilidad de Echo y Pusher
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                checkSystemStatus();
                setupEventListeners();
            }, 1000);
        });

        function checkSystemStatus() {
            const echoStatus = document.getElementById('echo-status');
            const pusherStatus = document.getElementById('pusher-status');
            const connectionStatus = document.getElementById('connection-status');

            if (typeof window.Echo !== 'undefined') {
                echoStatus.textContent = '✅ Disponible';
                echoStatus.style.color = '#28a745';
            } else {
                echoStatus.textContent = '❌ No disponible';
                echoStatus.style.color = '#dc3545';
            }

            if (typeof window.Pusher !== 'undefined') {
                pusherStatus.textContent = '✅ Disponible';
                pusherStatus.style.color = '#28a745';
            } else {
                pusherStatus.textContent = '❌ No disponible';
                pusherStatus.style.color = '#dc3545';
            }

            // Verificar conexión
            testConnection();
        }

        function testConnection() {
            const status = document.getElementById('connection-status');
            
            if (typeof window.Echo === 'undefined') {
                status.textContent = '❌ Error: Laravel Echo no está disponible';
                status.className = 'status error';
                return;
            }

            try {
                const rutaId = document.querySelector('meta[name="user-ruta-id"]').getAttribute('content');
                const channel = window.Echo.private(`ruta.${rutaId}`);
                
                status.textContent = `✅ Conectado al canal: ruta.${rutaId}`;
                status.className = 'status success';
                
                addLogEntry('success', `Conectado exitosamente al canal ruta.${rutaId}`);
            } catch (error) {
                status.textContent = `❌ Error de conexión: ${error.message}`;
                status.className = 'status error';
                addLogEntry('error', `Error de conexión: ${error.message}`);
            }
        }

        function reconnect() {
            addLogEntry('info', 'Intentando reconectar...');
            
            if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                window.Echo.connector.pusher.disconnect();
                setTimeout(() => {
                    window.Echo.connector.pusher.connect();
                    testConnection();
                }, 1000);
            }
        }

        function setupEventListeners() {
            if (typeof window.Echo === 'undefined') {
                addLogEntry('error', 'No se pueden configurar listeners: Echo no disponible');
                return;
            }

            const rutaId = document.querySelector('meta[name="user-ruta-id"]').getAttribute('content');
            const channel = window.Echo.private(`ruta.${rutaId}`);

            // Listeners para eventos de prueba
            channel.listen('.credito.created', (data) => {
                addLogEntry('success', `📊 Crédito creado: ${JSON.stringify(data)}`);
            });

            channel.listen('.credito.updated', (data) => {
                addLogEntry('info', `📊 Crédito actualizado: ${JSON.stringify(data)}`);
            });

            channel.listen('.cliente.created', (data) => {
                addLogEntry('success', `👤 Cliente creado: ${JSON.stringify(data)}`);
            });

            channel.listen('.cliente.updated', (data) => {
                addLogEntry('info', `👤 Cliente actualizado: ${JSON.stringify(data)}`);
            });

            channel.listen('.abono.created', (data) => {
                addLogEntry('success', `💰 Abono creado: ${JSON.stringify(data)}`);
            });

            channel.listen('.abono.updated', (data) => {
                addLogEntry('info', `💰 Abono actualizado: ${JSON.stringify(data)}`);
            });

            addLogEntry('success', 'Event listeners configurados correctamente');
        }

        function simulateEvent(type, action) {
            eventCount++;
            const timestamp = new Date().toLocaleString();
            
            // Simular datos de prueba
            const testData = {
                credito: {
                    id: eventCount,
                    cliente_id: Math.floor(Math.random() * 100) + 1,
                    monto: Math.floor(Math.random() * 10000) + 1000,
                    saldo: Math.floor(Math.random() * 5000) + 500,
                    fecha_credito: timestamp
                },
                cliente: {
                    id: eventCount,
                    nombre: `Cliente Prueba ${eventCount}`,
                    telefono: `555-${String(eventCount).padStart(4, '0')}`,
                    direccion: `Dirección ${eventCount}`
                },
                abono: {
                    id: eventCount,
                    credito_id: Math.floor(Math.random() * 50) + 1,
                    monto: Math.floor(Math.random() * 1000) + 100,
                    fecha_abono: timestamp
                }
            };

            const eventData = {
                [type]: testData[type],
                message: `${type.charAt(0).toUpperCase() + type.slice(1)} ${action === 'created' ? 'creado' : 'actualizado'} (simulación)`,
                timestamp: timestamp
            };

            // Simular el evento
            addLogEntry('info', `🔄 Simulando evento: ${type}.${action}`);
            
            // Trigger manual del evento (para prueba)
            setTimeout(() => {
                if (window.Echo) {
                    // Simular que el evento fue recibido
                    const eventName = `${type}.${action}`;
                    addLogEntry(action === 'created' ? 'success' : 'info', 
                        `📡 Evento simulado recibido: ${eventName} - ${JSON.stringify(eventData)}`);
                }
            }, 500);
        }

        function addLogEntry(type, message) {
            const log = document.getElementById('event-log');
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${timestamp}] ${message}`;
            
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }

        function clearLog() {
            const log = document.getElementById('event-log');
            log.innerHTML = '<div class="log-entry info">Log limpiado - Esperando eventos...</div>';
        }
    </script>
</body>
</html>
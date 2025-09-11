# Solución de Problemas WebSocket en Producción

## Error Actual
```
Pusher error: cURL error 28: Failed to connect to cabsisem.net.pe port 6001 after 29771 ms: Connection timed out
```

## Diagnóstico del Problema

El error indica que:
1. **El servidor WebSocket NO está ejecutándose** en el puerto 6001 del servidor de producción
2. **El puerto 6001 puede estar bloqueado** por firewall
3. **Cloudflare puede estar bloqueando** el tráfico WebSocket

## Solución Paso a Paso

### 1. Verificar Estado del Servidor

En el servidor de producción, ejecutar:

```bash
# Verificar si hay algún proceso en puerto 6001
sudo netstat -tlnp | grep :6001

# Verificar procesos de Laravel
ps aux | grep websockets

# Verificar estado de Supervisor
sudo supervisorctl status
```

### 2. Configurar Supervisor (CRÍTICO)

```bash
# Copiar archivo de configuración
sudo cp laravel-websockets.conf /etc/supervisor/conf.d/

# Recargar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-websockets

# Verificar estado
sudo supervisorctl status laravel-websockets
```

### 3. Configurar Firewall

```bash
# Permitir puerto 6001
sudo ufw allow 6001/tcp

# Verificar reglas
sudo ufw status
```

### 4. Configurar Cloudflare

**IMPORTANTE**: Cloudflare debe configurarse para permitir WebSockets:

1. **Panel de Cloudflare** → **Network**
2. **WebSockets**: Activar
3. **Firewall Rules**: Crear regla para permitir puerto 6001
4. **Page Rules**: Configurar bypass para `*.cabsisem.net.pe:6001`

### 5. Verificar Configuración DNS

```bash
# Verificar que el dominio apunte al servidor correcto
nslookup cabsisem.net.pe
dig cabsisem.net.pe
```

### 6. Probar Conexión

```bash
# Desde el servidor local
telnet cabsisem.net.pe 6001

# Desde el servidor de producción
telnet localhost 6001
```

### 7. Verificar Logs

```bash
# Logs de WebSocket
sudo tail -f /var/log/laravel-websockets.log

# Logs de errores
sudo tail -f /var/log/laravel-websockets-error.log

# Logs de Laravel
tail -f storage/logs/laravel.log
```

## Comandos de Gestión

### Supervisor
```bash
# Iniciar servicio
sudo supervisorctl start laravel-websockets

# Detener servicio
sudo supervisorctl stop laravel-websockets

# Reiniciar servicio
sudo supervisorctl restart laravel-websockets

# Ver estado
sudo supervisorctl status

# Ver logs en tiempo real
sudo supervisorctl tail -f laravel-websockets
```

### Manual (Solo para pruebas)
```bash
# Ejecutar manualmente (NO recomendado para producción)
cd /var/www/html/Cabsisem
php artisan websockets:serve --port=6001
```

## Configuración de Cloudflare Específica

### Page Rules
1. **URL**: `cabsisem.net.pe:6001/*`
2. **Settings**: 
   - Cache Level: Bypass
   - Security Level: Essentially Off
   - Browser Integrity Check: Off

### Firewall Rules
1. **Field**: Hostname
2. **Operator**: equals
3. **Value**: `cabsisem.net.pe`
4. **Action**: Allow

## Verificación Final

Después de aplicar todas las configuraciones:

```bash
# 1. Verificar proceso
sudo supervisorctl status laravel-websockets

# 2. Verificar puerto
sudo netstat -tlnp | grep :6001

# 3. Probar conexión local
telnet localhost 6001

# 4. Probar conexión externa
telnet cabsisem.net.pe 6001

# 5. Verificar logs
tail -f /var/log/laravel-websockets.log
```

## Notas Importantes

- **Supervisor es ESENCIAL** para mantener el servicio ejecutándose
- **El puerto 6001 debe estar abierto** en el firewall
- **Cloudflare debe permitir WebSockets** explícitamente
- **Los certificados SSL** deben estar configurados correctamente
- **El servicio se reinicia automáticamente** si falla

## Contacto de Emergencia

Si el problema persiste:
1. Verificar logs detallados
2. Contactar al administrador del servidor
3. Revisar configuración de Cloudflare
4. Verificar certificados SSL
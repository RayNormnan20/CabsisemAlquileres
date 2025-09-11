# Configuración de WebSockets para Producción

## Resumen

Este documento describe cómo configurar el sistema de WebSockets para funcionar en el entorno de producción de `cabsisem.net.pe`.

## Archivos Modificados

### 1. Configuración de Broadcasting (`config/broadcasting.php`)
- ✅ Configuración dinámica basada en `APP_ENV`
- ✅ SSL habilitado para producción
- ✅ Host configurado para `cabsisem.net.pe`

### 2. Configuración de WebSockets (`config/websockets.php`)
- ✅ `allowed_origins` configurado para el dominio de producción
- ✅ Soporte para SSL

### 3. Frontend JavaScript (`resources/js/bootstrap.js`)
- ✅ Detección automática de entorno (HTTP vs HTTPS)
- ✅ Configuración dinámica de host y puerto
- ✅ SSL automático en producción

### 4. Configuración de Filament (`config/filament.php`)
- ✅ Echo configurado para producción
- ✅ SSL habilitado automáticamente

## Variables de Entorno Requeridas

### Variables Críticas para Producción

```bash
# Configuración de la aplicación
APP_ENV=production
APP_URL=https://cabsisem.net.pe
ASSET_URL=https://cabsisem.net.pe
APP_FORCE_HTTPS=true

# Broadcasting
BROADCAST_DRIVER=pusher

# Pusher/WebSockets - DEBES CONFIGURAR ESTOS VALORES
PUSHER_APP_ID=tu_app_id_aqui
PUSHER_APP_KEY=tu_app_key_aqui
PUSHER_APP_SECRET=tu_app_secret_aqui
PUSHER_HOST=cabsisem.net.pe
PUSHER_PORT=6001
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Variables para Vite (Frontend)
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# SSL para WebSockets (Opcional si usas certificados)
LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT=/path/to/certificate.pem
LARAVEL_WEBSOCKETS_SSL_LOCAL_PK=/path/to/private.key
LARAVEL_WEBSOCKETS_SSL_PASSPHRASE=tu_passphrase_si_es_necesario
```

## Opciones de Configuración

### Opción 1: Usar Pusher.com (Recomendado para empezar)

1. Ve a [pusher.com](https://pusher.com) y crea una cuenta
2. Crea una nueva aplicación
3. Copia las credenciales a tu archivo `.env`
4. No necesitas configurar SSL adicional

### Opción 2: Laravel WebSockets (Servidor Propio)

1. Usa las credenciales que quieras en `.env`
2. Configura SSL con certificados válidos
3. Inicia el servidor WebSocket
4. Configura tu servidor web (Nginx/Apache)

## Pasos de Instalación en Producción

### 1. Preparar el Entorno

```bash
# Copiar archivo de configuración
cp .env.production.example .env

# Editar variables de entorno
nano .env
```

### 2. Ejecutar Script de Configuración

```bash
# Hacer ejecutable
chmod +x setup-production-websockets.sh

# Ejecutar configuración
./setup-production-websockets.sh
```

### 3. Configurar SSL (Solo para Laravel WebSockets)

Si usas Let's Encrypt:

```bash
# Agregar al .env
LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT=/etc/letsencrypt/live/cabsisem.net.pe/fullchain.pem
LARAVEL_WEBSOCKETS_SSL_LOCAL_PK=/etc/letsencrypt/live/cabsisem.net.pe/privkey.pem
```

### 4. Iniciar Servidor WebSocket

```bash
# Iniciar en background
nohup php artisan websockets:serve --port=6001 > websockets.log 2>&1 &

# Verificar que está funcionando
ps aux | grep websockets
```

### 5. Configurar Servidor Web

#### Para Nginx:

```nginx
# Agregar al bloque server
location /app/ {
    proxy_pass https://127.0.0.1:6001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}
```

#### Para Apache:

```apache
# Habilitar módulos
a2enmod proxy
a2enmod proxy_http
a2enmod proxy_wstunnel

# Agregar al VirtualHost
ProxyPass /app/ ws://127.0.0.1:6001/app/
ProxyPassReverse /app/ ws://127.0.0.1:6001/app/
```

### 6. Configurar Firewall

```bash
# Abrir puerto 6001
ufw allow 6001

# O para iptables
iptables -A INPUT -p tcp --dport 6001 -j ACCEPT
```

## Verificación

### 1. Verificar Servidor WebSocket

```bash
# Probar conexión
curl -k https://cabsisem.net.pe:6001/app/tu_app_key

# Debería devolver información de la aplicación
```

### 2. Verificar desde el Frontend

1. Abre la consola del navegador en `https://cabsisem.net.pe`
2. Verifica que no hay errores de WebSocket
3. Prueba crear un registro para ver actualizaciones en tiempo real

### 3. Monitorear Logs

```bash
# Logs de WebSocket
tail -f websockets.log

# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs del servidor web
tail -f /var/log/nginx/error.log
```

## Solución de Problemas

### Error: "Connection refused"
- Verificar que el servidor WebSocket está ejecutándose
- Verificar que el puerto 6001 está abierto
- Verificar configuración del firewall

### Error: "SSL certificate problem"
- Verificar que los certificados SSL son válidos
- Verificar rutas en las variables de entorno
- Verificar permisos de los archivos de certificado

### Error: "Origin not allowed"
- Verificar configuración de `allowed_origins` en `config/websockets.php`
- Agregar el dominio correcto

### Frontend no recibe eventos
- Verificar configuración de Echo en la consola del navegador
- Verificar que las variables VITE están correctas
- Recompilar assets: `npm run build`

## Mantenimiento

### Reiniciar Servidor WebSocket

```bash
# Encontrar proceso
ps aux | grep websockets

# Matar proceso
kill -9 PID

# Reiniciar
nohup php artisan websockets:serve --port=6001 > websockets.log 2>&1 &
```

### Actualizar Configuración

```bash
# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Recompilar assets
npm run build

# Reiniciar servidor WebSocket
```

## Monitoreo

### Verificar Estado del Servicio

```bash
# Script de verificación
#!/bin/bash
if pgrep -f "websockets:serve" > /dev/null; then
    echo "✅ WebSocket server is running"
else
    echo "❌ WebSocket server is not running"
    # Reiniciar automáticamente
    nohup php artisan websockets:serve --port=6001 > websockets.log 2>&1 &
fi
```

### Configurar Cron para Monitoreo

```bash
# Agregar a crontab
*/5 * * * * /path/to/check-websockets.sh
```

## Notas Importantes

1. **Seguridad**: Nunca expongas las credenciales de Pusher en el código fuente
2. **SSL**: En producción, siempre usa HTTPS/WSS
3. **Firewall**: Solo abre los puertos necesarios
4. **Monitoreo**: Configura alertas para el servidor WebSocket
5. **Backup**: Respalda la configuración regularmente

## Soporte

Si tienes problemas:

1. Revisa los logs de error
2. Verifica la configuración paso a paso
3. Prueba en un entorno de staging primero
4. Consulta la documentación de Laravel WebSockets

---

**Última actualización**: $(date)
**Versión**: 1.0
**Entorno**: Producción - cabsisem.net.pe
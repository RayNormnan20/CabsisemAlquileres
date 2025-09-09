# Gestión de Sesiones en Dispositivos Móviles

## Descripción
Este sistema implementa el cierre automático de sesión cuando los usuarios en dispositivos móviles salen de la página web, requiriendo que ingresen nuevamente su contraseña para acceder.

## Componentes Implementados

### 1. Middleware de Detección Móvil
**Archivo:** `app/Http/Middleware/MobileSessionManager.php`

- Detecta automáticamente dispositivos móviles y tablets usando la librería `jenssegers/agent`
- Marca las sesiones como móviles para el manejo diferenciado
- Procesa las solicitudes de logout automático desde JavaScript

### 2. JavaScript de Gestión de Sesión
**Archivo:** `resources/js/mobile-session.js`

- Detecta cuando el usuario está en un dispositivo móvil
- Escucha eventos de salida de página (`visibilitychange`, `beforeunload`, `pagehide`)
- Envía solicitud automática de logout cuando el usuario sale de la página
- Incluye un delay de 5 segundos para evitar cierres accidentales

### 3. Ruta de Logout Móvil
**Archivo:** `routes/web.php`

- Endpoint `/mobile-logout` que procesa el cierre de sesión automático
- Invalida la sesión y regenera el token CSRF
- Retorna respuesta JSON confirmando el cierre exitoso

### 4. Configuración de Middleware
**Archivo:** `app/Http/Kernel.php`

- Middleware agregado al grupo `web` para todas las rutas protegidas
- También disponible como middleware de ruta individual `mobile.session`

## Funcionamiento

1. **Detección:** El middleware detecta si el usuario está en un dispositivo móvil
2. **Monitoreo:** El JavaScript monitorea los eventos de salida de página
3. **Logout Automático:** Cuando el usuario sale de la página, se envía una solicitud AJAX para cerrar la sesión
4. **Validación:** Al regresar, el usuario debe ingresar nuevamente sus credenciales

## Eventos Monitoreados

- `visibilitychange`: Cuando la página se oculta o se muestra
- `beforeunload`: Antes de que la página se descargue
- `pagehide`: Cuando la página se oculta en el historial

## Configuración

### Delay de Logout
Puedes modificar el tiempo de espera antes del logout automático en el archivo `mobile-session.js`:

```javascript
setTimeout(() => {
    this.performLogout();
}, 5000); // 5 segundos por defecto
```

### Exclusión de Rutas
Para excluir rutas específicas del middleware, modifica el archivo `Kernel.php` o aplica el middleware selectivamente.

## Dependencias

- `jenssegers/agent`: Para detección de dispositivos móviles
- Laravel Session: Para manejo de sesiones
- Axios: Para solicitudes AJAX (incluido en Laravel)

## Notas Importantes

- La funcionalidad solo se activa en dispositivos móviles y tablets
- Los usuarios de escritorio no se ven afectados
- El sistema respeta las configuraciones de sesión existentes de Laravel
- Compatible con autenticación estándar y SSO (OIDC)
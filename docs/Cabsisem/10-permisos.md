# Roles y permisos

Control de acceso basado en roles y permisos mediante `spatie/laravel-permission` y Policies/Middlewares.

## Roles típicos (ajusta según tu operación)

- Admin
  - Gestión completa del sistema: usuarios, roles, permisos, configuración general.
  - Acceso a todas las rutas y recursos, limpieza de caché.

- Gestor
  - Gestión de créditos y abonos, renovación/cancelación.
  - Reportes y exportaciones; acceso a paneles de estadística.

- Cobrador
  - Acceso a su(s) `Ruta(s)` asignadas y clientes asociados.
  - Registro de abonos, Yape, y visualización de planillas.

- Auditor
  - Acceso de sólo lectura a reportes, log de actividades y exportaciones.

## Permisos sugeridos (nombres orientativos)

- `manage-rutas`: crear/editar rutas y asignaciones (`RutaUsuario`).
- `manage-creditos`: crear/editar/renovar/cancelar créditos.
- `view-creditos`: ver listados y detalle de créditos.
- `manage-abonos`: registrar/editar abonos.
- `view-reportes`: acceder a reportes, estadísticas y exportaciones.
- `manage-usuarios`: CRUD de usuarios, roles y permisos.
- `view-yape`: acceder y exportar pagos Yape.
- `manage-config`: cambiar settings (`spatie/laravel-settings`).

## Policies y middlewares

- Policy de `Ruta`: restringe acceso a detalle según asignación de usuario.
- Middleware `check.ruta.access`: valida acceso a `/rutas/{ruta}`.
- Usa `Gate::allows`/`authorize` en controladores para acciones sensibles.

## Recomendaciones

- Seeders de roles/permisos: define roles base y asignaciones en `Database\Seeders`.
- Filament Resources: usa `canView`, `canCreate`, `canEdit`, `canDelete` según permisos.
- Logs: registra acciones clave en `LogActividad` para trazabilidad.
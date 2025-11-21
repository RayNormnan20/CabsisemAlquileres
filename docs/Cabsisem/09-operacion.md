# Operación y mantenimiento

## Caché y rendimiento

- Endpoints útiles: `GET /api/cache-status`, `POST /api/cache-clear`, `POST /api/creditos/limpiar-cache`.
- Usa filtros por `ruta_id` para respuestas más rápidas cuando aplique.

## WebSockets

- Paquete: `beyondcode/laravel-websockets`.
- Gestionar servicio con `supervisorctl` (ver comandos en `MANUAL.txt`).
- Logs: `/var/log/laravel-websockets.log`.

## Exportaciones

- Excel: `maatwebsite/excel` para reportes.
- PDF: `barryvdh/laravel-dompdf` para generación de PDF (ej. Yape Cliente).

## Roles y permisos

- `spatie/laravel-permission` para roles/permisos.
- Usa Policies/Middlewares para control de acceso (ej. `check.ruta.access`).
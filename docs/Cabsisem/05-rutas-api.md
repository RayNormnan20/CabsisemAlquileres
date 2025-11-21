# Rutas API

Base: `/api/*`. Autenticación: `auth:sanctum` donde aplique.

- `GET /api/user` — retorna el usuario autenticado (requiere Sanctum).
- `GET /api/cache-status` — estado de caché, tiempos con/sin caché, totales de créditos y claves activas.
- `POST /api/cache-clear` — limpia todo el caché.

Créditos con caché

- `GET /api/creditos/activos?ruta_id={id?}` — lista de créditos activos (opcional filtrar por ruta).
- `GET /api/creditos/vencidos?ruta_id={id?}` — lista de créditos vencidos.
- `GET /api/creditos/estadisticas?ruta_id={id?}` — estadísticas agregadas.
- `GET /api/creditos/resumen?ruta_id={id?}` — resumen completo (activos, vencidos, estadísticas) + tiempo de respuesta.
- `POST /api/creditos/limpiar-cache` — limpia claves de caché del dominio créditos.

Notas

- Las respuestas incluyen `status`, `data`, `total` cuando aplica y `cached: true`.
- El driver de caché se define en `config/cache.php` y `.env`.

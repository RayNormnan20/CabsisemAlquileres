# Rutas Web

Autenticación y sesión

- `GET /login` — formulario de login (móvil/web).
- `POST /login` — autenticación.
- `GET /filament/login` — alias que redirige a `/login`.
- `POST /mobile-logout` — cierra sesión preservando contexto (ruta, clientes). Respuesta JSON.
- `POST /filament/logout` — cierra sesión y redirige a `/login`, preserva retorno deseado.

SSO (OIDC)

- `GET /oidc/redirect` — inicio de flujo OIDC.
- `GET /oidc/callback` — retorno del proveedor OIDC.

Panel y vistas

- `GET /dashboard` — vista principal autenticada.
- `GET /rutas/{ruta}` — detalle de ruta (middleware `auth` + `check.ruta.access`).

Créditos (acciones)

- `POST /creditos/actualizar` — actualizar crédito.
- `POST /creditos/renovar` — renovar crédito.
- `POST /creditos/cancelar` — cancelar crédito.
- `GET /creditos/activos` — vista/endpoint web de activos.
- `GET /creditos/vencidos` — vista/endpoint web de vencidos.
- `GET /creditos/estadisticas` — estadísticas.
- `GET /creditos/conceptos` — conceptos con caché.
- `POST /creditos/limpiar-cache` — limpiar caché (web).

Clientes/Yape

- `GET /creditos/{credito}/yape-cliente`
- `GET /clientes/{cliente}/yape-cliente-completo`
- `GET /clientes/{cliente}/yape-clientes`
- `GET /clientes/{id}` — info de cliente.
- `GET /cobradores-por-ruta/{rutaId}` — listado de cobradores por ruta.
- `GET /yape-cliente/{id}/pdf` — genera PDF de pagos.

Alquileres

- `GET /admin/pagos-alquiler/get-resumen-data/{alquilerId}` — JSON con resumen mensual/pagos.
# Introducción y arquitectura

Cabsisem es una plataforma de gestión de cobranzas y alquileres, construida sobre Laravel 9 con Filament como panel administrativo.

## Componentes principales

- Dominio de Créditos: `Clientes`, `Creditos`, `Abonos`, `Conceptos*`, `OrdenCobro`, `TipoPago`, `Ruta`, `RutaUsuario`.
- Dominio de Alquileres: `Alquiler`, `ClienteAlquiler`, `PagoAlquiler`, `Departamento`, `Edificio`, `EstadoDepartamento`.
- Administración y Referenciales: `Moneda`, `TipoDocumento`, `TipoCobro`, `Oficina`.
- Seguridad y control de acceso: `User`, `Role`, `Permission` (Spatie), Policies y middleware `CheckRutaAccess`.
- Auditoría: `LogActividad`.
- Operación y reportes: `PlanillaRecaudador`, `Movimiento`, módulos de resumen/estadísticas.
- Integraciones: WebSockets (real‑time), Excel/PDF, OIDC (SSO), Sanctum (API).

## UI/Admin (Filament)

- Recursos y páginas en `app/Filament` para CRUD y dashboards.
- Widgets con métricas y tablas en tiempo real.

## Internacionalización

- Traducciones en `lang/` para 60+ idiomas; interfaz y textos.
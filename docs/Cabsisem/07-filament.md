# Administración (Filament)

Filament es el panel administrativo de Cabsisem. Recursos y páginas clave en `app/Filament`.

## Páginas

- `Dashboard`: métricas y widgets principales.
- `ClienteCreditosAbonos`: vista integrada de créditos y abonos por cliente.
- `PagosDelDia`: consolidado de pagos diarios.
- `ReportesCristian`: reportes específicos.
- `TrasladarClientes`: herramientas para mover clientes entre rutas.
- Tableros: `Kanban` (proyectos) y `JiraImport` (integración opcional).
- Configuración: `ManageGeneralSettings`, `UserAccessSettings`.

## Recursos (CRUD)

- Créditos y cobranzas: `Clientes`, `Creditos`, `Abonos`, `Conceptos`, `ConceptosAbonos`, `CuadreRecaudador`, `PlanillaRecaudador`, `Rutas`.
- Alquileres: `Alquileres`, `PagosAlquiler`, `ResumenAlquiler`, `HistorialAlquileres`.
- Inventario: `Departamentos`, `Edificios`, `EstadoDepartamento`.
- Referenciales: `Oficinas`, `Monedas/Tipos` (según implementación).
- Seguridad: `User`, `Role`, `Permission`, `LogActividad`.
- Yape: `YapeCliente`.

## Campos y acciones (ejemplos)

- `UserResource`
  - Campos: `name`, `apellidos`, `celular`, `email`, `password`.
  - Validaciones: `required`, longitudes máximas, unicidad de `email`.
  - Acciones: crear, editar, reset de contraseña vía Breezy.

- `YapeClienteResource`
  - Campos: `nombre` (quien yapea), `user_id` (cobrador), `valor` (monto del crédito), `monto` (monto yape).
  - Comportamiento: opciones de `user_id` filtradas por usuarios con rutas; por defecto `Auth::id()`.
  - Acciones: exportar/generar PDF (`/yape-cliente/{id}/pdf`).

- `RutasResource`
  - Acciones dependientes de permisos `manage-rutas`.
  - Vista de clientes y créditos asociados.

## Widgets

- Métricas y tablas en tiempo real: `*WebSocketWidget`, `FinancialStatsWidget`, `ClientesPorRenovarWidget`, `PagosAlquiler*`, `UserAccessHoursWidget`, `Timesheet/*`.
- Yape: `YapeClientesTableWidget`, `YapesTotalesDelDiaWidget`, `YapeClientesWebSocketWidget`.

## Acceso y seguridad

- Breezy/Filament: ver `config/filament-breezy.php` para modelo de usuario, broker y campo de login.
- Policies y permisos: ver `docs/Cabsisem/10-permisos.md`.
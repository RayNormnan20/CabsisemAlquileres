# Modelos y módulos

Inventario de modelos en `app/Models` y su propósito.

- `Clientes`: gestión de clientes del sistema.
- `Creditos`: créditos activos/vencidos, saldo y estado.
- `Abonos`: pagos/abonos aplicados a créditos.
- `Concepto`, `ConceptoCredito`, `ConceptoAbono`: catálogo de conceptos de crédito/abono.
- `OrdenCobro`: órdenes de cobro emitidas.
- `TipoPago`, `TipoCobro`, `TipoDocumento`, `Moneda`: referenciales.
- `Ruta`, `RutaUsuario`: rutas de cobradores y asignaciones usuario‑ruta.
- `Oficina`: oficinas o sucursales.
- `PlanillaRecaudador`: planillas de cobranza y consolidado.
- `YapeCliente`: registro de pagos tipo Yape asociados a clientes/usuarios.
- `Movimiento`: movimientos financieros.
- `Alquiler`, `ClienteAlquiler`, `PagoAlquiler`: módulo de alquileres y pagos mensuales.
- `Departamento`, `Edificio`, `EstadoDepartamento`: inventario y estados de unidades.
- `LogActividad`: auditoría de acciones.
- `Role`, `Permission`, `User`: usuarios y roles/permiso (Spatie).

Relaciones destacadas

- `User` ↔ `Ruta` (many‑to‑many, pivot `usuario_ruta`, con principal `es_principal`).
- `Alquiler` tiene `pagos` con `usuarioRegistro` y `inquilino`.
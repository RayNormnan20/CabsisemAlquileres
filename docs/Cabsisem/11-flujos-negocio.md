# Flujos de negocio

Documentación funcional paso a paso de los procesos clave.

## Alta de crédito

- Precondiciones: cliente existente (`Clientes`), ruta asignada (`RutaUsuario`).
- Pasos:
  - Crear registro en `Creditos` con monto inicial y parámetros de cobro.
  - Asociar a `Cliente` y `Ruta` correspondiente.
  - Generar `OrdenCobro` si aplica.
  - Notificar (opcional) mediante Filament/Widgets.
  - Cache: invalida claves relacionadas (`conceptos_creditos_all`, estadísticas).

## Aplicar abono

- Trigger: desde Filament `Abonos` o pantalla de cliente.
- Pasos:
  - Registrar en `Abonos` con concepto (`ConceptoAbono`) y monto.
  - Actualizar `saldo_actual` en `Creditos`.
  - Registrar en `Movimiento` si corresponde a flujo financiero.
  - Cache: recalcular/reseteo parcial de `creditos_activos_all` y estadísticas.

## Renovación de crédito

- Endpoint: `POST /creditos/renovar` (ver `routes/web.php`).
- Pasos:
  - Validar estado del crédito y elegibilidad.
  - Actualizar términos/monto y generar nueva `OrdenCobro` si aplica.
  - Registrar `LogActividad` y mantener vínculo con cliente/ruta.
  - Cache: limpiar claves de créditos y estadísticas.

## Cancelación de crédito

- Endpoint: `POST /creditos/cancelar`.
- Pasos:
  - Verificar saldo, aplicar último abono si corresponde.
  - Marcar `Creditos` como cancelado, registrar razón.
  - Registrar `LogActividad`.
  - Cache: limpiar claves de créditos.

## Registro de Yape

- Recurso: `YapeCliente`.
- Pasos:
  - Capturar `monto`, `valor` (del crédito) y `user_id` (cobrador).
  - Asociar a `Cliente`/`Credito` si aplica.
  - Generar PDF: `GET /yape-cliente/{id}/pdf`.

## Consulta y detalle de Ruta

- Ruta: `GET /rutas/{ruta}`.
- Restricción: `auth` + `check.ruta.access`.
- Pasos:
  - Autorización: `access-ruta` + `manage-rutas` según acción.
  - Carga de relaciones: `clientes`, `creditos`, `usuario`, `oficina`.

## Resumen de alquiler

- Ruta: `GET /admin/pagos-alquiler/get-resumen-data/{alquilerId}`.
- Pasos:
  - Cargar `Alquiler` con `pagos` y `inquilino`.
  - Generar pagos mensuales desde `fecha_inicio` hasta `fecha_fin`/actual.
  - Agregar abonos y totales (ordenados por `fecha_pago`).
CREATE OR REPLACE VIEW vista_movimientos AS
SELECT
    a.id_abono AS id,
    'Abono' AS tipo_movimiento,
    a.fecha_pago AS fecha,
    a.monto_abono AS monto,
    c.nombre AS cliente,
    u.name AS usuario,
    co.nombre AS concepto,
    co.tipo AS tipo_concepto,
    a.observaciones AS observaciones,
    c.id_ruta AS id_ruta
FROM abonos a
JOIN clientes c ON c.id_cliente = a.id_cliente
JOIN users u ON u.id = a.id_usuario
JOIN conceptos co ON co.id = a.id_concepto

UNION ALL

SELECT
    cr.id_credito AS id,
    'Crédito' AS tipo_movimiento,
    cr.fecha_credito AS fecha,
    cr.valor_credito AS monto,
    c2.nombre AS cliente,
    (
        SELECT u2.name FROM users u2
        JOIN usuario_ruta ur ON ur.user_id = u2.id
        WHERE ur.id_ruta = cr.id_ruta
        LIMIT 1
    ) AS usuario,
    co2.nombre AS concepto,
    co2.tipo AS tipo_concepto,
    NULL AS observaciones,
    cr.id_ruta AS id_ruta
FROM creditos cr
JOIN clientes c2 ON c2.id_cliente = cr.id_cliente
JOIN conceptos co2 ON co2.id = cr.id_concepto;


SELECT * FROM vista_movimientos LIMIT 10;

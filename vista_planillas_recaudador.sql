CREATE OR REPLACE VIEW vista_planillas_recaudador AS
SELECT 
    CONCAT(c.id_cliente, '-', cr.id_credito) AS id_unico,
    c.id_cliente,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente_completo,
    c.celular AS telefono,
    cr.id_credito,
    cr.valor_credito,
    cr.saldo_actual,
    cr.numero_cuotas,
    cr.valor_cuota,
    cr.fecha_credito,
    cr.fecha_proximo_pago,
    cr.fecha_vencimiento,
    cr.id_ruta,
    cr.es_adicional,
    cr.porcentaje_interes,
    (
        SELECT a.fecha_pago 
        FROM abonos a 
        WHERE a.id_credito = cr.id_credito 
        ORDER BY a.fecha_pago DESC 
        LIMIT 1
    ) AS ultima_fecha_pago,
    (
        SELECT a.monto_abono 
        FROM abonos a 
        WHERE a.id_credito = cr.id_credito 
        ORDER BY a.fecha_pago DESC 
        LIMIT 1
    ) AS ultimo_monto_pagado,
    (
        SELECT IFNULL(SUM(a.monto_abono), 0)
        FROM abonos a 
        WHERE a.id_credito = cr.id_credito
    ) AS total_abonos,
    r.nombre AS ruta,
    GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS recaudador,
    MIN(u.id) AS id_recaudador,
    CASE 
        WHEN cr.saldo_actual <= 0 THEN 'PAGADO'
        WHEN cr.saldo_actual > 0 AND DATEDIFF(CURRENT_DATE, cr.fecha_vencimiento) > 0 THEN 'MOROSO'
        ELSE 'AL DÍA'
    END AS estado_credito,
    CASE 
        WHEN cr.es_adicional = 1 THEN 'ADICIONAL'
        ELSE 'REGULAR'
    END AS tipo_credito,
    CASE 
        WHEN cr.saldo_actual <= 0 THEN 0
        WHEN cr.fecha_vencimiento IS NULL THEN 0
        WHEN DATEDIFF(CURRENT_DATE, cr.fecha_vencimiento) > 0 THEN DATEDIFF(CURRENT_DATE, cr.fecha_vencimiento)
        ELSE 0
    END AS dias_atraso
FROM 
    clientes c
JOIN 
    creditos cr ON cr.id_cliente = c.id_cliente
JOIN 
    ruta r ON r.id_ruta = cr.id_ruta
JOIN 
    usuario_ruta ur ON ur.id_ruta = r.id_ruta
JOIN 
    users u ON u.id = ur.user_id
GROUP BY 
    c.id_cliente, cr.id_credito, c.nombre, c.apellido, c.celular, 
    cr.valor_credito, cr.saldo_actual, cr.numero_cuotas, cr.valor_cuota,
    cr.fecha_credito, cr.fecha_proximo_pago, cr.fecha_vencimiento,
    cr.id_ruta, cr.es_adicional, cr.porcentaje_interes, r.nombre
ORDER BY 
    r.nombre, c.nombre;

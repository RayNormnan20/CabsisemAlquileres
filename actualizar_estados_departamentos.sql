-- Script SQL para actualizar estados de departamentos con alquileres
-- Actualiza automáticamente a 'Ocupado' (id=2) los departamentos que tienen alquileres con fecha_fin NULL

-- Mostrar departamentos antes de la actualización
SELECT 
    'ANTES DE LA ACTUALIZACIÓN' as status,
    d.id_departamento,
    d.numero_departamento,
    e.nombre as edificio,
    ed.nombre as estado_actual,
    COUNT(a.id_alquiler) as total_alquileres,
    COUNT(CASE WHEN a.fecha_fin IS NULL THEN 1 END) as alquileres_sin_fecha_fin
FROM departamentos d
LEFT JOIN edificios e ON d.id_edificio = e.id_edificio
LEFT JOIN estados_departamento ed ON d.id_estado_departamento = ed.id_estado_departamento
LEFT JOIN alquileres a ON d.id_departamento = a.id_departamento
WHERE EXISTS (
    SELECT 1 FROM alquileres a2 
    WHERE a2.id_departamento = d.id_departamento 
    AND a2.fecha_fin IS NULL
)
GROUP BY d.id_departamento, d.numero_departamento, e.nombre, ed.nombre
ORDER BY e.nombre, d.numero_departamento;

-- Actualizar departamentos que tienen alquileres con fecha_fin NULL a estado 'Ocupado'
UPDATE departamentos 
SET id_estado_departamento = 2  -- 2 = Ocupado
WHERE id_departamento IN (
    SELECT DISTINCT a.id_departamento
    FROM alquileres a
    WHERE a.fecha_fin IS NULL
)
AND id_estado_departamento != 2;  -- Solo actualizar si no está ya ocupado

-- Mostrar departamentos después de la actualización
SELECT 
    'DESPUÉS DE LA ACTUALIZACIÓN' as status,
    d.id_departamento,
    d.numero_departamento,
    e.nombre as edificio,
    ed.nombre as estado_actual,
    COUNT(a.id_alquiler) as total_alquileres,
    COUNT(CASE WHEN a.fecha_fin IS NULL THEN 1 END) as alquileres_sin_fecha_fin
FROM departamentos d
LEFT JOIN edificios e ON d.id_edificio = e.id_edificio
LEFT JOIN estados_departamento ed ON d.id_estado_departamento = ed.id_estado_departamento
LEFT JOIN alquileres a ON d.id_departamento = a.id_departamento
WHERE EXISTS (
    SELECT 1 FROM alquileres a2 
    WHERE a2.id_departamento = d.id_departamento 
    AND a2.fecha_fin IS NULL
)
GROUP BY d.id_departamento, d.numero_departamento, e.nombre, ed.nombre
ORDER BY e.nombre, d.numero_departamento;

-- Estadísticas finales por estado
SELECT 
    'ESTADÍSTICAS FINALES' as reporte,
    ed.nombre as estado_departamento,
    COUNT(d.id_departamento) as total_departamentos,
    COUNT(CASE WHEN a.fecha_fin IS NULL THEN 1 END) as con_alquileres_null
FROM departamentos d
LEFT JOIN estados_departamento ed ON d.id_estado_departamento = ed.id_estado_departamento
LEFT JOIN alquileres a ON d.id_departamento = a.id_departamento
GROUP BY ed.id_estado_departamento, ed.nombre
ORDER BY ed.id_estado_departamento;

-- Verificación: Mostrar todos los alquileres con fecha_fin NULL y el estado de sus departamentos
SELECT 
    'VERIFICACIÓN ALQUILERES NULL' as reporte,
    a.id_alquiler,
    e.nombre as edificio,
    d.numero_departamento,
    a.fecha_inicio,
    a.fecha_fin,
    ed.nombre as estado_departamento,
    CASE 
        WHEN ed.nombre = 'Ocupado' THEN '✓ CORRECTO'
        ELSE '✗ INCORRECTO'
    END as validacion
FROM alquileres a
INNER JOIN departamentos d ON a.id_departamento = d.id_departamento
INNER JOIN edificios e ON d.id_edificio = e.id_edificio
LEFT JOIN estados_departamento ed ON d.id_estado_departamento = ed.id_estado_departamento
WHERE a.fecha_fin IS NULL
ORDER BY e.nombre, d.numero_departamento;
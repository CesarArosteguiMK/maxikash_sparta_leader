-- Eliminar todo rastro de Indicadores de permisos (módulos de eficiencia, KPI, etc.).
-- Ejecutar en la BD del proyecto después de haber quitado rutas y menú en código.
-- IDs: 24-27, 29-40 (KPI Total, Gestión 1-7, Eficiencia 1-7, Gestión 8-21, Eficiencia 8-21,
--       Intensidad, Detalle Clientes, Detalle Eficiencia, Cartera Inicial, Promesas Pago,
--       Espartanos, Matriz Buckets, Buckets +1, Auditoría, Auditoría 2, Seguimiento).

-- 1) Quitar asignaciones de esos módulos a cualquier usuario
DELETE FROM asigna_modulo_web
WHERE modulo_web_id IN (24, 25, 26, 27, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40);

-- 2) Desactivar los módulos en modulos_web (así dejan de listarse en Capital Humano)
UPDATE modulos_web
SET activo = 0
WHERE id IN (24, 25, 26, 27, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40);

-- Opcional: si prefieres borrar los registros de modulos_web en lugar de desactivarlos,
-- descomenta la siguiente línea y comenta el UPDATE de arriba:
-- DELETE FROM modulos_web WHERE id IN (24, 25, 26, 27, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40);

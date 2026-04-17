-- ═══════════════════════════════════════════════════════════════
-- Migración: Separación por Célula en Cierre de Crédito
-- Fecha: 2026-04-16
-- ═══════════════════════════════════════════════════════════════

-- 1. Agregar columna id_celula a convenio_cliente
ALTER TABLE convenio_cliente
    ADD COLUMN id_celula TINYINT UNSIGNED DEFAULT NULL
    COMMENT '1=Despachos, 2=Call Center'
    AFTER estatus;

-- 2. Crear índice para filtrar rápido
ALTER TABLE convenio_cliente
    ADD INDEX idx_convenio_celula (id_celula);

-- 3. Poblar id_celula en convenios existentes desde asigna_creditos_despacho
UPDATE convenio_cliente cc
INNER JOIN (
    SELECT acd.id_credito, acd.id_celula
    FROM asigna_creditos_despacho acd
    WHERE acd.estatus = '1'
      AND acd.id_celula IS NOT NULL
    ORDER BY acd.fecha_alta DESC
) src ON src.id_credito = cc.id_credito
SET cc.id_celula = src.id_celula
WHERE cc.id_celula IS NULL;

-- 4. Agregar columna id_celula a cierre_credito_seguimiento (para historial)
ALTER TABLE cierre_credito_seguimiento
    ADD COLUMN id_celula TINYINT UNSIGNED DEFAULT NULL
    COMMENT '1=Despachos, 2=Call Center'
    AFTER email_destino_cartera;

-- 5. Poblar id_celula en seguimiento existente desde convenio_cliente
UPDATE cierre_credito_seguimiento ccs
INNER JOIN convenio_cliente cc ON cc.id_credito = ccs.id_credito
SET ccs.id_celula = cc.id_celula
WHERE ccs.id_celula IS NULL AND cc.id_celula IS NOT NULL;

-- 6. Nuevos módulos_web para permisos de célula
--    56 = Cierre: Despachos (célula 1)
--    57 = Cierre: Call Center (célula 2)
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES
    (56, 'Cierre: Despachos',    'Cierre de crédito', 'Filtra la vista de Cierre de Crédito a célula 1 (Despachos)',    1),
    (57, 'Cierre: Call Center',  'Cierre de crédito', 'Filtra la vista de Cierre de Crédito a célula 2 (Call Center)',  1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion);

-- ═══════════════════════════════════════════════════════════════
-- NOTAS:
-- - Si un usuario tiene SOLO módulo 56 → ve solo célula 1 (Despachos)
-- - Si un usuario tiene SOLO módulo 57 → ve solo célula 2 (Call Center)
-- - Si tiene ambos (56+57) o ninguno → ve todo (backward compatible)
-- - Los convenios sin id_celula (NULL) se muestran a todos
-- ═══════════════════════════════════════════════════════════════
--  AACASTILLO
-- __SPARTA_PASSWORD_REDACTED__
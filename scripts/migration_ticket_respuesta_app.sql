SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'respuesta_resultado') = 0,
    'ALTER TABLE ticket ADD COLUMN respuesta_resultado VARCHAR(20) NULL AFTER url_direccion',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'respuesta_comentario') = 0,
    'ALTER TABLE ticket ADD COLUMN respuesta_comentario TEXT NULL AFTER respuesta_resultado',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'respuesta_fecha') = 0,
    'ALTER TABLE ticket ADD COLUMN respuesta_fecha DATETIME NULL AFTER respuesta_comentario',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'respuesta_id_persona') = 0,
    'ALTER TABLE ticket ADD COLUMN respuesta_id_persona INT NULL AFTER respuesta_fecha',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO estado_ticket (nombre, orden, activo)
SELECT 'Procesando', 3, 1
WHERE NOT EXISTS (
    SELECT 1 FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'procesando'
);

INSERT INTO estado_ticket (nombre, orden, activo)
SELECT 'Aceptado', 4, 1
WHERE NOT EXISTS (
    SELECT 1 FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'aceptado'
);

INSERT INTO estado_ticket (nombre, orden, activo)
SELECT 'Denegado', 5, 1
WHERE NOT EXISTS (
    SELECT 1 FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'denegado'
);

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ticket'
      AND INDEX_NAME = 'idx_ticket_panel_categoria_estado_fecha'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_ticket_panel_categoria_estado_fecha ON ticket (categoria_gestion, activo, fecha_eliminacion, fecha_creacion)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'dictamen'
      AND INDEX_NAME = 'idx_dictamen_ticket_id'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_dictamen_ticket_id ON dictamen (id_ticket, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'dictamen_sistema'
      AND INDEX_NAME = 'idx_ds_ticket_id'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_ds_ticket_id ON dictamen_sistema (id_ticket, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignacion_ticket'
      AND INDEX_NAME = 'idx_asig_ticket_activo_fecha'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_asig_ticket_activo_fecha ON asignacion_ticket (id_ticket, activo, fecha_asignacion)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'adj_operacion'
      AND INDEX_NAME = 'idx_adj_op_estatus_fecha'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_adj_op_estatus_fecha ON adj_operacion (estatus, fecha_alta)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'adj_historial_estatus'
      AND INDEX_NAME = 'idx_hist_estatus_op'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_hist_estatus_op ON adj_historial_estatus (estatus_nuevo, id_operacion)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'adj_dictamen'
      AND INDEX_NAME = 'idx_dictamen_op_fecha_id'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_dictamen_op_fecha_id ON adj_dictamen (id_operacion, fecha_alta, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asigna_creditos_adjudicacion'
      AND INDEX_NAME = 'idx_aca_credito_estatus_id'
);
SET @sql := IF(
    @idx = 0,
    'CREATE INDEX idx_aca_credito_estatus_id ON asigna_creditos_adjudicacion (id_credito, estatus, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

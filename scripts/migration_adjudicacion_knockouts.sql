-- Knockouts de adjudicacion: REPUVE y condicion fisica evaluada por IA.
-- Es aditiva y se puede ejecutar una sola vez en la base principal de Sparta.

CREATE TABLE IF NOT EXISTS adj_validacion_knockout (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_solicitud BIGINT UNSIGNED NULL,
    id_operacion INT NULL,
    tipo VARCHAR(40) NOT NULL,
    estado VARCHAR(40) NOT NULL DEFAULT 'PENDIENTE',
    etiqueta VARCHAR(100) NULL,
    proveedor VARCHAR(50) NULL,
    modelo VARCHAR(120) NULL,
    confianza TINYINT UNSIGNED NULL,
    motivo VARCHAR(1000) NULL,
    detalle_json JSON NULL,
    media_hash CHAR(64) NULL,
    intentos INT UNSIGNED NOT NULL DEFAULT 0,
    fecha_alta DATETIME NOT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    fecha_resolucion DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY ux_adj_knockout_solicitud_tipo (id_solicitud, tipo),
    UNIQUE KEY ux_adj_knockout_operacion_tipo (id_operacion, tipo),
    KEY idx_adj_knockout_pendiente (tipo, estado, fecha_actualizacion),
    CONSTRAINT fk_adj_knockout_solicitud FOREIGN KEY (id_solicitud) REFERENCES adj_solicitud(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_adj_knockout_operacion FOREIGN KEY (id_operacion) REFERENCES adj_operacion(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

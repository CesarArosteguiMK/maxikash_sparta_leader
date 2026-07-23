-- Etapa 2: expediente de campo posterior a Luz Verde.
-- La evidencia multimedia continúa almacenándose en adj_evidencia.

CREATE TABLE IF NOT EXISTS adj_gestion_campo (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_solicitud BIGINT UNSIGNED NOT NULL,
    id_operacion INT NOT NULL,
    id_credito BIGINT NOT NULL,
    estatus VARCHAR(50) NOT NULL DEFAULT 'pendiente_captura',
    evidencia_perfil VARCHAR(30) NOT NULL DEFAULT 'etapa2_2026',
    legacy_user_id BIGINT NULL,
    legacy_external_id VARCHAR(100) NULL,
    acta_datos_json JSON NULL,
    acta_firma_estatus VARCHAR(40) NOT NULL DEFAULT 'no_generada',
    fad_requisicion_id BIGINT NULL,
    notificacion_luz_verde_at DATETIME NULL,
    notificacion_luz_verde_resultado VARCHAR(40) NULL,
    vobo_estatus VARCHAR(40) NOT NULL DEFAULT 'no_solicitado',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    creado_por INT NULL,
    creado_por_nombre VARCHAR(180) NULL,
    fecha_alta DATETIME NOT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY ux_adj_gestion_campo_solicitud (id_solicitud),
    UNIQUE KEY ux_adj_gestion_campo_operacion (id_operacion),
    KEY idx_adj_gestion_campo_credito (id_credito),
    KEY idx_adj_gestion_campo_estatus (estatus, fecha_actualizacion),
    CONSTRAINT fk_adj_gestion_campo_solicitud
        FOREIGN KEY (id_solicitud) REFERENCES adj_solicitud (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS adj_gestion_campo_evento (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_gestion_campo BIGINT UNSIGNED NOT NULL,
    evento VARCHAR(80) NOT NULL,
    estatus_anterior VARCHAR(50) NULL,
    estatus_nuevo VARCHAR(50) NOT NULL,
    actor_id INT NULL,
    actor_nombre VARCHAR(180) NULL,
    metadata_json JSON NULL,
    fecha DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_adj_gestion_campo_evento_fecha (id_gestion_campo, fecha),
    CONSTRAINT fk_adj_gestion_campo_evento_gestion
        FOREIGN KEY (id_gestion_campo) REFERENCES adj_gestion_campo (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


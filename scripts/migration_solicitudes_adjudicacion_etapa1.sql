-- Etapa 1 / Sprint 0: solicitudes multicanal de adjudicacion.
-- Esta migracion es aditiva y no modifica adj_operacion ni el pipeline vigente.

CREATE TABLE IF NOT EXISTS adj_solicitud (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    folio VARCHAR(40) NOT NULL,
    id_credito BIGINT NOT NULL,
    canal VARCHAR(30) NOT NULL,
    estatus VARCHAR(50) NOT NULL DEFAULT 'recibida',
    nombre_cliente VARCHAR(180) NULL,
    entregara_titular TINYINT(1) NOT NULL,
    nombre_entregante VARCHAR(180) NULL,
    kilometraje INT UNSIGNED NULL,
    telefono_actual VARCHAR(20) NULL,
    direccion_resguardo VARCHAR(500) NULL,
    motivo VARCHAR(1000) NULL,
    id_usuario_solicitante INT NOT NULL,
    nombre_usuario_solicitante VARCHAR(180) NULL,
    id_operacion INT NULL,
    idempotency_key VARCHAR(100) NULL,
    datos_credito_json JSON NULL,
    payload_original JSON NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    fecha_alta DATETIME NOT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    id_credito_activo BIGINT GENERATED ALWAYS AS (
        CASE
            WHEN deleted_at IS NULL
             AND estatus NOT IN ('cancelada', 'rechazada', 'completada', 'blacklist')
            THEN id_credito
            ELSE NULL
        END
    ) STORED,
    PRIMARY KEY (id),
    UNIQUE KEY ux_adj_solicitud_uuid (uuid),
    UNIQUE KEY ux_adj_solicitud_folio (folio),
    UNIQUE KEY ux_adj_solicitud_idempotency (idempotency_key),
    UNIQUE KEY ux_adj_solicitud_credito_activo (id_credito_activo),
    KEY idx_adj_solicitud_credito (id_credito),
    KEY idx_adj_solicitud_canal_estatus (canal, estatus),
    KEY idx_adj_solicitud_solicitante_fecha (id_usuario_solicitante, fecha_alta),
    KEY idx_adj_solicitud_operacion (id_operacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS adj_solicitud_historial (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_solicitud BIGINT UNSIGNED NOT NULL,
    evento VARCHAR(80) NOT NULL,
    estatus_anterior VARCHAR(50) NULL,
    estatus_nuevo VARCHAR(50) NOT NULL,
    comentario VARCHAR(1000) NULL,
    actor_id INT NULL,
    actor_nombre VARCHAR(180) NULL,
    actor_canal VARCHAR(30) NULL,
    metadata_json JSON NULL,
    fecha DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_adj_solicitud_historial_solicitud_fecha (id_solicitud, fecha),
    CONSTRAINT fk_adj_solicitud_historial_solicitud
        FOREIGN KEY (id_solicitud) REFERENCES adj_solicitud (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
